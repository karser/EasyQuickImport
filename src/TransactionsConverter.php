<?php declare(strict_types=1);

namespace App;

use App\Currency\CurrencyMap;
use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksAccountRepositoryInterface;
use App\SheetScheduler\Transaction;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class TransactionsConverter
{
    const FIELD_DATE = 'date';
    const FIELD_CURRENCY = 'currency';
    const FIELD_MEMO = 'memo';
    const FIELD_ACCOUNT = 'account';
    const FIELD_AMOUNT = 'amount';

    public const GNUCASH_FIELDS_MAPPING = [
        self::FIELD_DATE => 'Date',
        self::FIELD_CURRENCY => 'Commodity/Currency',
        self::FIELD_MEMO => 'Description',
        self::FIELD_ACCOUNT => 'Full Account Name',
        self::FIELD_AMOUNT => 'Amount With Sym',
    ];

    const DEFAULT_EXPENSE_ACCOUNT = 'Ask My Accountant';

    private $csvEncoder;
    private $normalizer;
    private $accountRepository;
    private $accountsMapping;
    private $currencyMap;

    public function __construct(CsvEncoder $encoder, NormalizerInterface $normalizer,
                                QuickbooksAccountRepositoryInterface $accountRepository,
                                string $accountsMappingPath)
    {
        $this->csvEncoder = $encoder;
        $this->normalizer = $normalizer;
        $this->accountRepository = $accountRepository;
        $accountsMappingJson = file_get_contents($accountsMappingPath);
        Assert::string($accountsMappingJson);
        $this->accountsMapping = json_decode($accountsMappingJson, true, 512, JSON_THROW_ON_ERROR);
        $this->currencyMap = new CurrencyMap();
    }

    public function convertWrapper(UploadedFile $file, string $qbUsername): string
    {
        Assert::eq('text/plain', $file->getMimeType());
        $inputFilePath = $file->getRealPath();
        Assert::string($inputFilePath);
        $inputFile = file_get_contents($inputFilePath);
        Assert::string($inputFile);
        $inputData = $this->csvEncoder->decode($inputFile, 'csv');
        $entities = $this->convert($inputData, $qbUsername);
        $result = $this->csvEncoder->encode($entities, 'csv');
        Assert::string($result);
        return $result;
    }

    /**
     * @param array<int, array<string, string>> $inputData
     * @param array<string, string> $fieldsMapping
     *
     * @return array<int, Transaction>
     */
    public function convert(array $inputData, string $qbUsername, ?array $fieldsMapping = null): array
    {
        $fieldsMapping = $fieldsMapping ?? self::GNUCASH_FIELDS_MAPPING;
        Assert::true(count($inputData) % 2 === 0, 'The amount of rows must be even');

        $odds = $evens = [];
        foreach ($inputData as $k => $v) {
            if ($k % 2 === 0) {
                $evens[] = $v;
            } else {
                $odds[] = $v;
            }
        }

        $entities = [];
        foreach ($evens as $i => $evenItem) {
            $oddItem = $odds[$i];

            $entity = new Transaction();
            $date = $evenItem[$fieldsMapping[self::FIELD_DATE]] ?? null;
            Assert::string($date, "Line {$i}:");
            $date = \DateTime::createFromFormat('d/m/y', $date);
            Assert::isInstanceOf($date, \DateTime::class, "Line {$i}:");
            $entity->setTxnDate($date->format('Y-m-d'));

//            Assert::notNull($currency = $evenItem[$mapping[self::FIELD_CURRENCY]] ?? null, "Line {$i}:");
//            Assert::eq(1, preg_match('/CURRENCY::(.+)/', $currency, $matches), "Line {$i}: Currency in wrong format");
//            [, $currency] = $this->currencyMap->findCurrency($matches[1]);
//            $entity->setCurrency($currency);


            $description = $evenItem[$fieldsMapping[self::FIELD_MEMO]] ?? null;
            Assert::notNull($description, "Line {$i}:");
            $entity->setCreditMemo($description);
            $entity->setDebitMemo($description);

            Assert::string($evenAccountMapped = $evenItem[$fieldsMapping[self::FIELD_ACCOUNT]] ?? null, "Line {$i}:");
            $evenAccount = $this->accountsMapping[$qbUsername][$evenAccountMapped] ?? null;
            Assert::string($evenAccount, "Line {$i}: Account not mapped: {$evenAccountMapped}");
            $qbEvenAccount = $this->accountRepository->findOneByName($qbUsername, $evenAccount);
            Assert::notNull($qbEvenAccount, "Line {$i}: Unable to find account: {$evenAccount}");
            Assert::eq(QuickbooksAccount::TYPE_BANK, $qbEvenAccount->getAccountType(), "Line {$i}: Type of {$evenAccount} must be bank");

            [$evenAmount, $evenCurrency, $evenNeg] = $this->getAmount($evenItem, $fieldsMapping[self::FIELD_AMOUNT]);
            [$oddAmount, $oddCurrency, $oddNeg] = $this->getAmount($oddItem, $fieldsMapping[self::FIELD_AMOUNT]);

            if ($evenNeg === $oddNeg) {
                if ($oddAmount === '0.00' ) {
                    $oddNeg = !$evenNeg;
                } else if ($evenAmount === '0.00') {
                    $evenNeg = !$oddNeg;
                }
            }
            Assert::notEq($evenNeg, $oddNeg, "Line {$i}: Sign of both amount is the same");

            Assert::string($oddAccountMapped = $oddItem[$fieldsMapping[self::FIELD_ACCOUNT]] ?? null, "Line {$i}:");
            $oddAccount = $this->accountsMapping[$qbUsername][$oddAccountMapped]
                ?? ($evenNeg ? self::DEFAULT_EXPENSE_ACCOUNT : QuickbooksAccount::UNCATEGORIZED_INCOME);
//            Assert::string($oddAccount, "Account not mapped: {$oddAccountMapped}");
            $qbOddAccount = $this->accountRepository->findOneByName($qbUsername, $oddAccount);
            Assert::notNull($qbOddAccount, "Line {$i}: Unable to find account: {$oddAccount}");

            $useEvenAmount = $qbOddAccount->getAccountType() !== QuickbooksAccount::TYPE_BANK;
            $oddAmount = $useEvenAmount ? $evenAmount : $oddAmount;

            Assert::eq($evenCurrency, $qbEvenAccount->getCurrency(), "Line {$i}: Currencies of {$evenAccount} do not match");
            $entity->setCurrency($evenCurrency);


            [$creditAmount, $debitAmount] = $evenNeg ? [$evenAmount, $oddAmount] : [$oddAmount, $evenAmount];
            [$creditAccount, $debitAccount] = $evenNeg ? [$evenAccount, $oddAccount] : [$oddAccount, $evenAccount];

            $entity->setCreditAccount($creditAccount);
            $entity->setCreditAmount($creditAmount);

            $entity->setDebitAccount($debitAccount);
            $entity->setDebitAmount($debitAmount);


            $entities[] = $entity;
        }

        $result = $this->normalizer->normalize($entities);
        Assert::isArray($result);

        return $result;
    }

    /**
     * @param array<string, string> $item
     * @return array{0: string, 1: string, 2: bool}
     */
    private function getAmount(array $item, string $key): array
    {
        $amount = $item[$key] ?? null;
        Assert::string($amount);
        Assert::eq(1, preg_match('/(-*?)([^0-9.,-]+)([0-9.,]+)/', $amount, $matches), "Amount in wrong format: {$amount}");
        [, $currency] = $this->currencyMap->findCurrency($matches[2]);

        return [$matches[3], $currency, $matches[1] === '-'];
    }
}
