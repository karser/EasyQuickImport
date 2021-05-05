<?php declare(strict_types=1);

namespace App\Tests\Unit\Currency;

use App\Currency\CurrencyExchangerInterface;
use App\Currency\UpdateRateOnEntityScheduledSubscriber;
use App\Entity\QuickbooksCompany;
use App\SheetScheduler\EntityOnScheduledEvent;
use App\SheetScheduler\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateRateOnEntityScheduledSubscriberTest extends TestCase
{
    private const TEST_USER = 'test_user';

    /** @var QuickbooksCompany */
    private $user;
    /** @var CurrencyExchangerInterface|MockObject */
    private $exchanger;
    /** @var UpdateRateOnEntityScheduledSubscriber */
    private $subscriber;

    public function setUp(): void
    {
        $this->user = new QuickbooksCompany(self::TEST_USER);
        $this->user->setBaseCurrency('HKD');
        $this->exchanger = $this->getMockBuilder(CurrencyExchangerInterface::class)->getMock();
        $this->subscriber = new UpdateRateOnEntityScheduledSubscriber($this->exchanger);
    }

    public function testSetExchangeRateIfNotSet(): void
    {
        $t1 = new Transaction();
        $t1->setTxnDate('2018-10-10');
        $t1->setCurrency('US Dollar');

        $this->exchanger->expects(self::once())->method('getExchangeRate')
            ->with('HKD', 'USD', '2018-10-10')
            ->willReturn(8);
        $event = new EntityOnScheduledEvent($this->user, [$t1], 0);
        $this->subscriber->onScheduled($event);

        self::assertSame('8', $t1->getExchangeRate());
    }

    public function testSkipsIfExchangeRateIsSet(): void
    {
        $t1 = new Transaction();
        $t1->setTxnDate('2018-10-10');
        $t1->setCurrency('US Dollar');
        $t1->setExchangeRate('8');

        $this->exchanger->expects(self::never())->method('getExchangeRate');

        $event = new EntityOnScheduledEvent($this->user, [$t1], 0);
        $this->subscriber->onScheduled($event);

        self::assertSame('8', $t1->getExchangeRate());
    }

    public function testIrrelevantObjectsSkipped(): void
    {
        $obj = new \stdClass();
        $obj->a = 'b';

        $this->exchanger->expects(self::never())->method('getExchangeRate');
        $event = new EntityOnScheduledEvent($this->user, [$obj], 0);
        $this->subscriber->onScheduled($event);
    }
}
