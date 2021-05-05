<?php declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksAccountRepositoryInterface;
use App\Entity\QuickbooksCompany;
use App\TransactionsConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TransactionsConverterTest extends TestCase
{
    private const COMPANY1 = 'acme1';
    private const COMPANY2 = 'acme2';

    /** @var CsvEncoder */
    private $csvEncoder;
    /** @var QuickbooksAccountRepositoryInterface&MockObject */
    private $accountRepository;
    /** @var TransactionsConverter */
    private $converter;

    public function setUp(): void
    {
        $this->csvEncoder = new CsvEncoder();
        $this->accountRepository = $this->getMockBuilder(QuickbooksAccountRepositoryInterface::class)->getMock();
        $serializer = new Serializer([new ObjectNormalizer()], [$this->csvEncoder]);
        $this->converter = new TransactionsConverter($this->csvEncoder, $serializer, $this->accountRepository, __DIR__ . '/fixtures/accounts_mapping.json');

        $this->accountRepository->method('findOneByName')->willReturnCallback(function ($qbUsername, $name): ?QuickbooksAccount {
            $acc = new QuickbooksAccount();
            $acc->setCompany(new QuickbooksCompany($qbUsername));
            $acc->setFullName($name);
            switch ($name) {
                case 'HDFC HKD Savings':
                    $acc->setCurrency('Hong Kong Dollar');
                    $acc->setAccountType(QuickbooksAccount::TYPE_BANK);
                    break;
                case 'Bank Service Charges':
                    $acc->setCurrency('Hong Kong Dollar');
                    $acc->setAccountType(QuickbooksAccount::TYPE_EXPENSE);
                    break;
                case 'Citibank EUR':
                    $acc->setCurrency('Euro');
                    $acc->setAccountType(QuickbooksAccount::TYPE_BANK);
                    break;
                case 'Citibank USD':
                    $acc->setCurrency('US Dollar');
                    $acc->setAccountType(QuickbooksAccount::TYPE_BANK);
                    break;
                case 'Interest Income':
                    $acc->setCurrency('Hong Kong Dollar');
                    $acc->setAccountType(QuickbooksAccount::TYPE_OTHER_INCOME);
                    break;
                case 'Uncategorized Income':
                    $acc->setCurrency('Hong Kong Dollar');
                    $acc->setAccountType(QuickbooksAccount::TYPE_INCOME);
                    break;
                default:
                    return null;
            }
            return $acc;
        });
    }

    public function testConvertExpenseHkd(): void
    {
        $input = [
            [
                'Date' => '07/04/18',
                'Transaction ID' => '5ba07172d1b673a5523cff1e26a9bb2f',
                'Number' => '',
                'Description' => 'Monthly fee',
                'Notes' => '',
                'Commodity/Currency' => 'CURRENCY::HKD',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Assets:Current Assets:HDFC Savings',
                'Account Name' => 'HDFC Savings',
                'Amount With Sym' => '-HK$1,200.00',
                'Amount Num' =>
                    [
                        '' => '-1,200.00',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '1.00',
            ],
            [
                'Date' => '',
                'Transaction ID' => '',
                'Number' => '',
                'Description' => '',
                'Notes' => '',
                'Commodity/Currency' => '',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Expense:Bank Charges HKD',
                'Account Name' => 'Bank Charges HKD',
                'Amount With Sym' => 'HK$1,200.00',
                'Amount Num' =>
                    [
                        '' => '1,200.00',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '1.00',
            ]
        ];

        $output = $this->converter->convert($input, self::COMPANY1);

        self::assertSame([
            [
                'txnDate' => '2018-04-07',
                'refNumber' => NULL,
                'currency' => 'Hong Kong Dollar',
                'exchangeRate' => NULL,
                'creditAccount' => 'HDFC HKD Savings',
                'creditMemo' => 'Monthly fee',
                'creditAmount' => '1,200.00',
                'debitAccount' => 'Bank Service Charges',
                'debitMemo' => 'Monthly fee',
                'debitAmount' => '1,200.00',
            ]
        ], $output);
    }

    public function testConvertBankToBankTransferUsdToEuro(): void
    {
        $input = [
            [
                'Date' => '17/01/19',
                'Transaction ID' => '3a37539cccd9465622266a2444dab907',
                'Number' => '',
                'Description' => 'Line Management / Negative Balance Balancing - 10300002-13045786-00014885 EUR ACME LIMITED',
                'Notes' => '',
                'Commodity/Currency' => 'CURRENCY::USD',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Assets:Current Assets:Citibank EUR',
                'Account Name' => 'Citibank EUR',
                'Amount With Sym' => '€9.80',
                'Amount Num' =>
                    [
                        '' => '9.80',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '1 + 32/245',
            ],
            [
                'Date' => '',
                'Transaction ID' => '',
                'Number' => '',
                'Description' => '',
                'Notes' => '',
                'Commodity/Currency' => '',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Assets:Current Assets:Citibank USD',
                'Account Name' => 'Citibank USD',
                'Amount With Sym' => '-$11.08',
                'Amount Num' =>
                    [
                        '' => '-11.08',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '1.00',
            ]
        ];

        $output = $this->converter->convert($input, self::COMPANY2);

        self::assertSame([
            [
                'txnDate' => '2019-01-17',
                'refNumber' => NULL,
                'currency' => 'Euro',
                'exchangeRate' => NULL,
                'creditAccount' => 'Citibank USD',
                'creditMemo' => 'Line Management / Negative Balance Balancing - 10300002-13045786-00014885 EUR ACME LIMITED',
                'creditAmount' => '11.08',
                'debitAccount' => 'Citibank EUR',
                'debitMemo' => 'Line Management / Negative Balance Balancing - 10300002-13045786-00014885 EUR ACME LIMITED',
                'debitAmount' => '9.80',
            ]
        ], $output);
    }

    public function testConvertUsdExpenseFromEuroAccount(): void
    {
        $input = [
            array (
                'Date' => '28/12/18',
                'Transaction ID' => '9d4f8a03907dccbc2af3fea2bcc238a6',
                'Number' => '',
                'Description' => 'Account management fee',
                'Notes' => '',
                'Commodity/Currency' => 'CURRENCY::EUR',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Assets:Current Assets:Citibank EUR',
                'Account Name' => 'Citibank EUR',
                'Amount With Sym' => '-€9.80',
                'Amount Num' =>
                    array (
                        '' => '-9.80',
                    ),
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '1.00',
            ),
            array (
                'Date' => '',
                'Transaction ID' => '',
                'Number' => '',
                'Description' => '',
                'Notes' => '',
                'Commodity/Currency' => '',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Expense:Bank Charges USD',
                'Account Name' => 'Bank Charges USD',
                'Amount With Sym' => '$11.65',
                'Amount Num' =>
                    array (
                        '' => '11.65',
                    ),
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '196/233',
            )
        ];

        $output = $this->converter->convert($input, self::COMPANY2);

        self::assertSame([
            [
                'txnDate' => '2018-12-28',
                'refNumber' => NULL,
                'currency' => 'Euro',
                'exchangeRate' => NULL,
                'creditAccount' => 'Citibank EUR',
                'creditMemo' => 'Account management fee',
                'creditAmount' => '9.80',
                'debitAccount' => 'Bank Service Charges',
                'debitMemo' => 'Account management fee',
                'debitAmount' => '9.80',
            ]
        ], $output);
    }

    public function testConvertIncomeHKD(): void
    {
        $input = [
            [
                'Date' => '28/09/18',
                'Transaction ID' => '25d6e938c44fdea473fa25d0f879e4fb',
                'Number' => '',
                'Description' => 'CREDIT INTEREST',
                'Notes' => '',
                'Commodity/Currency' => 'CURRENCY::HKD',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Assets:Current Assets:HDFC Savings',
                'Account Name' => 'HDFC Savings',
                'Amount With Sym' => 'HK$0.01',
                'Amount Num' =>
                    [
                        '' => '0.01',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '1.00',
            ],
            [
                'Date' => '',
                'Transaction ID' => '',
                'Number' => '',
                'Description' => '',
                'Notes' => '',
                'Commodity/Currency' => '',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Income:Interest income',
                'Account Name' => 'Interest income',
                'Amount With Sym' => '$0.00',
                'Amount Num' =>
                    [
                        '' => '0.00',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '0.00',
            ]
        ];

        $output = $this->converter->convert($input, self::COMPANY1);

        self::assertSame([
            [
                'txnDate' => '2018-09-28',
                'refNumber' => NULL,
                'currency' => 'Hong Kong Dollar',
                'exchangeRate' => NULL,
                'creditAccount' => 'Interest Income',
                'creditMemo' => 'CREDIT INTEREST',
                'creditAmount' => '0.01',
                'debitAccount' => 'HDFC HKD Savings',
                'debitMemo' => 'CREDIT INTEREST',
                'debitAmount' => '0.01',
            ]
        ], $output);
    }

    public function testConvertIncomeUsdToHkd(): void
    {
        $input = [
            [
                'Date' => '01/01/20',
                'Transaction ID' => '17fdb9fe2ec0d4a84533e96094875971',
                'Number' => '',
                'Description' => 'Withdrawal Conversion from: $173.19 USD Conversion to: $1,307.53 HKD Exchange rate: 7.5496846',
                'Notes' => '',
                'Commodity/Currency' => 'CURRENCY::USD',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Assets:Current Assets:HDFC Savings',
                'Account Name' => 'HDFC Savings',
                'Amount With Sym' => 'HK$1,307.53',
                'Amount Num' =>
                    [
                        '' => '1,307.53',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '17319/130753',
            ],
            [
                'Date' => '',
                'Transaction ID' => '',
                'Number' => '',
                'Description' => '',
                'Notes' => '',
                'Commodity/Currency' => '',
                'Void Reason' => '',
                'Action' => '',
                'Memo' => '',
                'Full Account Name' => 'Assets:Current Assets:Paypal USD',
                'Account Name' => 'Paypal USD',
                'Amount With Sym' => '-$173.19',
                'Amount Num' =>
                    [
                        '' => '-173.19',
                    ],
                'Reconcile' => 'n',
                'Reconcile Date' => '',
                'Rate/Price' => '1',
            ]
        ];

        $output = $this->converter->convert($input, self::COMPANY1);

        self::assertSame([
            [
                'txnDate' => '2020-01-01',
                'refNumber' => NULL,
                'currency' => 'Hong Kong Dollar',
                'exchangeRate' => NULL,
                'creditAccount' => 'Uncategorized Income',
                'creditMemo' => 'Withdrawal Conversion from: $173.19 USD Conversion to: $1,307.53 HKD Exchange rate: 7.5496846',
                'creditAmount' => '1,307.53',
                'debitAccount' => 'HDFC HKD Savings',
                'debitMemo' => 'Withdrawal Conversion from: $173.19 USD Conversion to: $1,307.53 HKD Exchange rate: 7.5496846',
                'debitAmount' => '1,307.53',
            ]
        ], $output);
    }
}
