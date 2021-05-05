<?php declare(strict_types=1);

namespace App;

use App\Entity\QuickbooksCompany;
use App\Exception\NotFoundException;
use App\Exception\RuntimeException;
use App\Exception\ValidationException;
use App\Exception\ValidationsException;
use App\SheetScheduler\Customer;
use App\SheetScheduler\CustomerInvoice;
use App\SheetScheduler\EntityOnScheduledEvent;
use App\SheetScheduler\FileDecoder;
use App\SheetScheduler\Transaction;
use App\SheetScheduler\TransformerResolver;
use App\SheetScheduler\Vendor;
use App\SheetScheduler\VendorBill;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use QuickBooks_QBXML_Object;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class SheetScheduler
{
    public const TYPE_CUSTOMER_INVOICE = 'invoice';
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_VENDOR_BILL = 'bill';
    public const TYPE_VENDOR = 'vendor';
    public const TYPE_TRANSACTION = 'transaction';

    const CLASS_MAP = [
        self::TYPE_CUSTOMER_INVOICE => CustomerInvoice::class,
        self::TYPE_CUSTOMER => Customer::class,
        self::TYPE_VENDOR_BILL => VendorBill::class,
        self::TYPE_VENDOR => Vendor::class,
        self::TYPE_TRANSACTION => Transaction::class,
    ];

    /** @var FilesystemInterface */
    private $sheetFilesystem;
    /** @var FileDecoder */
    private $fileDecoder;
    /** @var DenormalizerInterface */
    private $denormalizer;
    /** @var ValidatorInterface */
    private $validator;
    /** @var QuickbooksServerInterface */
    private $quickbooksServer;
    /** @var TransformerResolver */
    private $transformerResolver;
    /** @var QuickbooksFormatter */
    private $quickbooksFormatter;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    private $propertyAccessor;

    private array $dateFields = [];

    public function __construct(QuickbooksServerInterface $quickbooksServer,
                                FilesystemInterface $sheetFilesystem,
                                FileDecoder $fileDecoder,
                                DenormalizerInterface $denormalizer,
                                ValidatorInterface $validator,
                                TransformerResolver $transformerResolver,
                                QuickbooksFormatter $quickbooksFormatter,
                                EventDispatcherInterface $eventDispatcher,
                                PropertyAccessorInterface $propertyAccessor)
    {
        $this->quickbooksServer = $quickbooksServer;
        $this->sheetFilesystem = $sheetFilesystem;
        $this->fileDecoder = $fileDecoder;
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->transformerResolver = $transformerResolver;
        $this->quickbooksFormatter = $quickbooksFormatter;
        $this->eventDispatcher = $eventDispatcher;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function schedule(QuickbooksCompany $user, string $type, UploadedFile $file, ?array $fieldsMapping = null, ?string $dateFormat = null): int
    {
        $toSchedule = $this->prepare($user, $type, $file, $fieldsMapping, $dateFormat);
        foreach ($toSchedule as [$action, $id, $xml]) {
            $xml = $this->quickbooksFormatter->formatForOutput($xml);
            $this->quickbooksServer->schedule($user->getQbUsername(), $action, (string)$id, $xml);
        }

        return count($toSchedule);
    }

    public function dryRun(QuickbooksCompany $user, string $type, UploadedFile $file, ?array $fieldsMapping = null, ?string $dateFormat = null): string
    {
        $toSchedule = $this->prepare($user, $type, $file, $fieldsMapping, $dateFormat);
        $output = '';
        foreach ($toSchedule as [$action, $id, $xml]) {
            $output .= $xml;
        }
        return $this->quickbooksFormatter->formatForOutput($output);
    }

    public function loadFile(UploadedFile $file, ?array $fieldsMapping = null, ?int $limit = null): array
    {
        $path = $file->getRealPath();
        Assert::string($path);
        $items = $this->fileDecoder->decodeFile($path, $file->getMimeType());
        if (null !== $limit && count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
        }
        if (is_array($fieldsMapping) && count($fieldsMapping) > 0) {
            $items = array_map(static function($input) use ($fieldsMapping): array {
                $output = [];
                foreach ($fieldsMapping as $keyOutput => $keyInput) {
                    if (null !== $keyInput && isset($input[$keyInput])) {
                        $output[$keyOutput] = $input[$keyInput];
                    }
                }
                return $output;
            }, $items);
        }

        return $items;
    }

    public function canonizeDate(array $entities, string $sourceFormat, string $targetFormat = 'Y-m-d'): array
    {
        if (count($entities) === 0) {
            return [];
        }
        $dateFields = $this->getDateFields(get_class($entities[0]));
        foreach ($entities as $entity) {
            foreach ($dateFields as $dateField) {
                if (null !== ($date = $this->propertyAccessor->getValue($entity, $dateField)) &&
                    false !== $dateInstance = \DateTime::createFromFormat($sourceFormat, $date)
                ) {
                    $this->propertyAccessor->setValue($entity, $dateField, $dateInstance->format($targetFormat));
                }
            }
        }
        return $entities;
    }

    public function denormalize(string $type, array $items): array
    {
        $class = $this->getClass($type);
        try {
            /** @var array $entities */
            $entities = $this->denormalizer->denormalize($items, $class . '[]');
        } catch (ExceptionInterface $e) {
            throw new RuntimeException('Unable to denormalize array of objects', $e->getCode(), $e);
        }
        return $entities;
    }

    public function validateAllEntities(QuickbooksCompany $user, array $entities): void
    {
        $exceptions = [];
        foreach ($entities as $idx => $entity) {
            $line = $idx + 1;
            $errorList = $this->validator->validate($entity);
            if ($errorList->count() > 0) {
                $exceptions[] = new ValidationException($errorList, "Validation error on line number: {$line}");
            }
            $event = new EntityOnScheduledEvent($user, [$entity], $line);
            $this->eventDispatcher->dispatch($event);
            foreach ($event->getEntities() as $eventIdx => $eventEntity) {
                $errorList = $this->validator->validate($eventEntity);
                if ($errorList->count() > 0) {
                    $exceptions[] = new ValidationException($errorList, "Validation error on line number: {$line}");
                }
            }
        }
        if (count($exceptions) > 0) {
            throw new ValidationsException('Validation failed', $exceptions);
        }
    }

    private function prepare(QuickbooksCompany $company, string $type, UploadedFile $file, ?array $fieldsMapping = null, ?string $dateFormat = null): array
    {
        $items = $this->loadFile($file, $fieldsMapping);
        $entities = $this->denormalize($type, $items);
        if (null !== $dateFormat) {
            $entities = $this->canonizeDate($entities, $dateFormat);
        }
        $this->validateAllEntities($company, $entities);

        $remotePath = $file->getClientOriginalName();
        Assert::string($remotePath);
        $class = $this->getClass($type);
        $transformer = $this->transformerResolver->resolve($class);
        $transformer->setCompany($company);
        $toSchedule = [];
        foreach ($entities as $idx => $entity) {
            $line = $idx + 1;
            $event = new EntityOnScheduledEvent($company, [$entity], $line);
            $this->eventDispatcher->dispatch($event);
            foreach ($event->getEntities() as $eventIdx => $eventEntity) {
                $qbEntities = $transformer->transform($eventEntity);
                /**
                 * @var string $action
                 * @var QuickBooks_QBXML_Object $qbEntity
                 */
                foreach ($qbEntities as [$action, $qbEntity]) {
                    $xml = $qbEntity->asQBXML($action);
                    $id = $this->shrinkIdent($remotePath, (string)$line, (string)$eventIdx);
                    $toSchedule[] = [$action, $id, $xml];
                }
            }
        }
        return $toSchedule;
    }

    public function shrinkIdent(string $filename, string $line, string $eventIdx): string
    {
        $maxIdentLen = 40;
        $rest = ':'.$line.':'.$eventIdx;
        $allowedFilenameLen = $maxIdentLen - mb_strlen($rest);
        Assert::greaterThan($allowedFilenameLen, 0);
        if (mb_strlen($filename) > $allowedFilenameLen) {
            $hashLen = min(3, $allowedFilenameLen);
            $hash = mb_substr(md5($filename), 0, $hashLen);
            $filename = mb_substr($filename, 0, $allowedFilenameLen - $hashLen) . $hash;
            Assert::eq(mb_strlen($filename), $allowedFilenameLen);
        }
        return mb_substr($filename.$rest, -40);
    }

    public function copyToLocal(string $remotePath): UploadedFile
    {
        try {
            $content = $this->sheetFilesystem->read($remotePath);
        } catch (FileNotFoundException $e) {
            throw new NotFoundException("File {$remotePath} not found", $e->getCode(), $e);
        }
        $path = tempnam(sys_get_temp_dir(), 'import');
        if (false === $path || false === file_put_contents($path, $content)) {
            throw new RuntimeException('Unable to save the file');
        }
        $origName = basename($remotePath);
        return new UploadedFile($path, $origName);
    }

    private function getClass(string $type): string
    {
        if (!isset(self::CLASS_MAP[$type])) {
            throw new RuntimeException("Type {$type} is not supported");
        }
        return self::CLASS_MAP[$type];
    }

    private function getDateFields(string $class): array
    {
        if (!isset($this->dateFields[$class])) {
            $dateFields = [];
            /** @var ClassMetadata $metadata */
            $metadata = $this->validator->getMetadataFor($class);
            foreach ($metadata->properties as $property) {
                foreach ($property->constraints as $constraint) {
                    if ($constraint instanceof Date) {
                        $dateFields[] = $property->name;
                    }
                }
            }
            $this->dateFields[$class] = $dateFields;
        }
        return $this->dateFields[$class];
    }
}
