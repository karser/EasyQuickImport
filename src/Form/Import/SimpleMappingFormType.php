<?php declare(strict_types=1);

namespace App\Form\Import;

use App\Entity\Import;
use App\SheetScheduler;
use App\SheetScheduler\Customer;
use App\SheetScheduler\CustomerInvoice;
use App\SheetScheduler\FileDecoder;
use App\SheetScheduler\Transaction;
use App\SheetScheduler\Vendor;
use App\SheetScheduler\VendorBill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Webmozart\Assert\Assert;

class SimpleMappingFormType extends AbstractType
{
    private $fileDecoder;
    private $propertyInfoExtractor;

    public function __construct(FileDecoder $fileDecoder, PropertyInfoExtractorInterface $propertyInfoExtractor)
    {
        $this->fileDecoder = $fileDecoder;
        $this->propertyInfoExtractor = $propertyInfoExtractor;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Import|null $import */
        $import = $options['import'] ?? null;
        Assert::isInstanceOf($import, Import::class);

        $this->buildMapping($builder, $import);
    }

    private const REQUIRED_FIELDS = [
        CustomerInvoice::class => ['line1ItemName'],
        Customer::class => [],
        VendorBill::class => [],
        Vendor::class => [],
        Transaction::class => [],
    ];

    private function buildMapping(FormBuilderInterface $builder, Import $import): void
    {
        $type = $import->getImportType();
        Assert::string($type);
        $class = SheetScheduler::CLASS_MAP[$type];
        $fields = $this->propertyInfoExtractor->getProperties($class) ?? [];
        $choices = $this->getChoices($import);
        foreach ($fields as $field) {
            $required = isset(self::REQUIRED_FIELDS[$class]) && in_array($field, self::REQUIRED_FIELDS[$class], true);
            $builder
                ->add($field, ChoiceType::class, [
                    'placeholder' => 'Select field from your file',
                    'choices' => $choices,
                    'required' => $required,
                    'constraints' => $required ? [new NotBlank()] : [],
                    'data' => in_array($field, $choices, true) ? $field : null,
                ])
            ;
        }
    }

    private function getChoices(Import $import): array
    {
        $uploadedFile = $import->getFile();
        Assert::notNull($uploadedFile);
        $realPath = $uploadedFile->getRealPath();
        Assert::string($realPath);
        $data = $this->fileDecoder->decodeFile($realPath, $uploadedFile->getMimeType());
        Assert::notEmpty($data);
        $keys = array_keys($data[0]);
        $labels = array_map([$this, 'concatLabel'], $keys, $data[0]);
        $choices = array_combine($labels, $keys);
        Assert::isArray($choices);

        return $choices;
    }

    /**
     * @param mixed $value
     */
    public function concatLabel(string $key, $value): string
    {
        if ($value === null || $value === '') {
            return $key;
        }
        if (is_array($value)) {
            $value = implode(', ', array_filter($value));
        }
        $maxValueLen = 30 - mb_strlen($key);
        if (mb_strlen($value) > $maxValueLen) {
            $value = mb_substr($value, 0, $maxValueLen - 3) . '...';
        }
        return "{$key} ({$value})";
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'import' => null,
        ]);
    }
}
