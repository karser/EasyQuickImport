<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksCompany;
use App\Entity\User;
use App\Repository\QuickbooksAccountRepository;
use App\SheetScheduler;
use App\SheetScheduler\CustomerInvoice;
use App\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SchedulerTest extends WebTestCase
{
    const TEST_USER = 'quickbooks';

    /** @var QuickbooksCompany */
    private $company;
    /** @var User */
    private $user;

    /** @var SheetScheduler */
    private $scheduler;

    public function setUp()
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::$container->get(EntityManagerInterface::class);
        if (null === $company = $em->find(QuickbooksCompany::class, self::TEST_USER)) {
            $company = new QuickbooksCompany(self::TEST_USER);
            $em->persist($company);
        }
        $company->setMultiCurrencyEnabled(true);
        $em->flush();
        $this->company = $company;

        $accountRepo = static::$container->get(QuickbooksAccountRepository::class);
        $accountRepo->deleteAll($this->company);

        /** @var UserService $userService */
        $userService = static::$container->get(UserService::class);
        $this->user = $userService->createUser('admin@localhost', 'pass', ['ROLE_ADMIN']);

        $this->scheduler = static::$container->get(SheetScheduler::class);
    }

    public function provider(): array
    {
        return [
            ['vendor.csv', SheetScheduler::TYPE_VENDOR, 'vendor_converted.xml'],
            ['bill.csv', SheetScheduler::TYPE_VENDOR_BILL, 'bill_converted.xml'],
            ['customer.csv', SheetScheduler::TYPE_CUSTOMER, 'customer_converted.xml'],
            ['invoice.csv', SheetScheduler::TYPE_CUSTOMER_INVOICE, 'invoice_converted.xml'],
            ['transaction.csv', SheetScheduler::TYPE_TRANSACTION, 'transaction_converted.xml'],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testExchange(string $inputFile, string $type, string $expectedOuputFile): void
    {
        /** @var SheetScheduler $scheduler */
        $scheduler = static::$container->get(SheetScheduler::class);
        $expected = file_get_contents(__DIR__.'/fixtures/'.$expectedOuputFile);
        $actual = $scheduler->dryRun($this->company, $type, $scheduler->copyToLocal($inputFile));
        self::assertSame($expected, $actual);
    }

    public function testAccountRepo(): void
    {
        /** @var EntityManagerInterface $em */
        $em = static::$container->get(EntityManagerInterface::class);
        $accountRepo = static::$container->get(QuickbooksAccountRepository::class);

        $account = new QuickbooksAccount();
        $account->setCompany($this->company);
        $account->setFullName('Bank USD');
        $account->setAccountType(QuickbooksAccount::TYPE_BANK);
        $account->setCurrency('US Dollar');
        $account->setUser($this->user);
        $em->persist($account);
        $em->flush();

        $currency = $accountRepo->getCurrency(self::TEST_USER, 'Bank USD', QuickbooksAccount::TYPE_BANK);
        self::assertSame('US Dollar', $currency);

        $currency = $accountRepo->getCurrency(self::TEST_USER, 'Bank USD');
        self::assertSame('US Dollar', $currency);

        $currency = $accountRepo->getCurrency(self::TEST_USER, 'Bank', QuickbooksAccount::TYPE_BANK);
        self::assertNull($currency);

        $currency = $accountRepo->getCurrency('', 'Bank USD', QuickbooksAccount::TYPE_BANK);
        self::assertNull($currency);

        $currency = $accountRepo->getCurrency(self::TEST_USER, 'Bank USD', QuickbooksAccount::TYPE_EXPENSE);
        self::assertNull($currency);
    }

    public function testIdent()
    {
        /** @var SheetScheduler $scheduler */
        $scheduler = static::$container->get(SheetScheduler::class);

        self::assertSame('long-transactions-2856:1:JournalEntryAdd',
            $scheduler->shrinkIdent('long-transactions-20200401-20200419.csv', '1', 'JournalEntryAdd'));

        self::assertSame('long-invoices-20200bdc:2:JournalEntryAdd',
            $scheduler->shrinkIdent('long-invoices-20200401-20200419.csv', '2', 'JournalEntryAdd'));

        self::assertSame('long-transactions-856:11:JournalEntryAdd',
            $scheduler->shrinkIdent('long-transactions-20200401-20200419.csv', '11', 'JournalEntryAdd'));

        self::assertSame('lon856:11:JournalEntryAddJournalEntryAdd',
            $scheduler->shrinkIdent('long-transactions-20200401-20200419.csv', '11', 'JournalEntryAddJournalEntryAdd'));
    }

    public function testIdentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a value greater than 0. Got: -9');

        /** @var SheetScheduler $scheduler */
        $scheduler = static::$container->get(SheetScheduler::class);

        self::assertSame('lon856:11:JournalEntryAddJournalEntryAddJournalEntryAdd',
            $scheduler->shrinkIdent('long-transactions-20200401-20200419.csv', '11', 'JournalEntryAddJournalEntryAddJournalEntryAdd'));
    }

    public function testDateFormat()
    {
        $inv = new CustomerInvoice();
        $inv->setTxnDate('Apr 15, 2020');
        $this->scheduler->canonizeDate([$inv], 'M j, Y');
        self::assertSame('2020-04-15', $inv->getTxnDate());
    }

    public function testDateFormatIncorrect()
    {
        $inv = new CustomerInvoice();
        $inv->setTxnDate('AAA 15, 2020');
        $this->scheduler->canonizeDate([$inv], 'M j, Y');
        self::assertSame('AAA 15, 2020', $inv->getTxnDate());
    }
}
