<?php declare(strict_types=1);

namespace App\Tests\Unit\SheetScheduler;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksAccountRepositoryInterface;
use App\SheetScheduler\SplitTransactionsSubscriber;
use App\Entity\QuickbooksCompany;
use App\SheetScheduler\EntityOnScheduledEvent;
use App\SheetScheduler\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SplitTransactionsSubscriberTest extends TestCase
{
    private const TEST_USER = 'test_user';

    /** @var QuickbooksCompany */
    private $user;
    /** @var EntityManagerInterface|MockObject */
    private $em;
    /** @var QuickbooksAccountRepositoryInterface|MockObject */
    private $accountRepo;

    /** @var SplitTransactionsSubscriber */
    private $subscriber;

    public function setUp(): void
    {
        $this->user = new QuickbooksCompany(self::TEST_USER);
        $this->accountRepo = $this->getMockBuilder(QuickbooksAccountRepositoryInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->em->method('getRepository')->willReturnMap([
            [QuickbooksAccount::class, $this->accountRepo],
        ]);

        $this->subscriber = new SplitTransactionsSubscriber($this->em);
    }

    public function testIrrelevantObjects(): void
    {
        $obj = new \stdClass();
        $obj->a = 'b';

        $event = new EntityOnScheduledEvent($this->user, [$obj], 0);
        $this->subscriber->onScheduled($event);

        self::assertSame([$obj], $event->getEntities());
    }

    public function testTransferSameCurrency(): void
    {
        $transaction = new Transaction();
        $transaction->setTxnDate('2018-10-10');
        $transaction->setRefNumber('ref1');
        $transaction->setCurrency('US Dollar');
        $transaction->setExchangeRate('7.83704');
        $transaction->setCreditAccount('Bank1 USD');
        $transaction->setCreditMemo('credit memo');
        $transaction->setCreditAmount('400.00');
        $transaction->setDebitAccount('Bank2 USD');
        $transaction->setDebitMemo('debit memo');
        $transaction->setDebitAmount('400.00');

        $event = new EntityOnScheduledEvent($this->user, [$transaction], 0);
        $this->subscriber->onScheduled($event);

        self::assertSame([$transaction], $event->getEntities());
    }

    public function testTransferDifferentCurrency(): void
    {
        $transaction = new Transaction();
        $transaction->setTxnDate('2018-10-10');
        $transaction->setRefNumber('ref1');
        $transaction->setCurrency('US Dollar');
        $transaction->setCreditAccount('Bank USD');
        $transaction->setCreditMemo('credit memo');
        $transaction->setCreditAmount('500.00');
        $transaction->setDebitAccount('Bank EUR');
        $transaction->setDebitMemo('debit memo');
        $transaction->setDebitAmount('433.33');

        $this->accountRepo->method('getCurrency')->willReturnMap([
            [self::TEST_USER, 'Bank USD', QuickbooksAccount::TYPE_BANK, 'US Dollar'],
            [self::TEST_USER, 'Bank EUR', QuickbooksAccount::TYPE_BANK, 'Euro'],
        ]);


        $event = new EntityOnScheduledEvent($this->user, [$transaction], 0);
        $this->subscriber->onScheduled($event);

        self::assertCount(2, $event->getEntities());
        [$e1, $e2] = $event->getEntities();
        $normalizer = new ObjectNormalizer();
        $actual = $normalizer->normalize($e1);
        $expected = [
            'txnDate' => '2018-10-10',
            'refNumber' => 'ref1',
            'currency' => 'US Dollar',
            'exchangeRate' => null,
            'creditAccount' => 'Bank USD',
            'creditMemo' => 'credit memo',
            'creditAmount' => '500.00',
            'debitAccount' => QuickbooksAccount::UNDEPOSITED_FUNDS,
            'debitMemo' => 'debit memo',
            'debitAmount' => '500.00',
        ];
        self::assertEquals($expected, $actual);

        $actual = $normalizer->normalize($e2);
        $expected = [
            'txnDate' => '2018-10-10',
            'refNumber' => 'ref1',
            'currency' => 'Euro',
            'exchangeRate' => SplitTransactionsSubscriber::ID,
            'creditAccount' => QuickbooksAccount::UNDEPOSITED_FUNDS,
            'creditMemo' => 'credit memo',
            'creditAmount' => '433.33',
            'debitAccount' => 'Bank EUR',
            'debitMemo' => 'debit memo',
            'debitAmount' => '433.33',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testUpdatesReversedRate(): void
    {
        $t1 = new Transaction();
        $t1->setTxnDate('2018-10-10');
        $t1->setCurrency('US Dollar');
        $t1->setCreditAccount('Bank USD');
        $t1->setCreditAmount('500.00');
        $t1->setDebitAccount(QuickbooksAccount::UNDEPOSITED_FUNDS);
        $t1->setDebitAmount('500.00');
        $t1->setExchangeRate('8');

        $t2 = new Transaction();
        $t2->setTxnDate('2018-10-10');
        $t2->setCurrency('Euro');
        $t2->setCreditAccount(QuickbooksAccount::UNDEPOSITED_FUNDS);
        $t2->setCreditAmount('433.33');
        $t2->setDebitAccount('Bank EUR');
        $t2->setDebitAmount('433.33');
        $t2->setExchangeRate(SplitTransactionsSubscriber::ID);

        $this->accountRepo->method('getCurrency')->willReturnMap([
            [self::TEST_USER, 'Bank USD', QuickbooksAccount::TYPE_BANK, 'US Dollar'],
            [self::TEST_USER, 'Bank EUR', QuickbooksAccount::TYPE_BANK, 'Euro'],
        ]);

        $event = new EntityOnScheduledEvent($this->user, [$t1, $t2], 0);
        $this->subscriber->afterExchangeRateUpdated($event);

        self::assertCount(2, $event->getEntities());
        /** @var Transaction[] $entities */
        $entities = $event->getEntities();
        [$e1, $e2] = $entities;
        self::assertSame('9.2308402372326', $e2->getExchangeRate());
    }
}
