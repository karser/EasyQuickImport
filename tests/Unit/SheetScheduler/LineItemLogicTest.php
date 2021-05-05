<?php declare(strict_types=1);

namespace App\Tests\Unit\SheetScheduler;

use App\Exception\RuntimeException;
use App\SheetScheduler\LineItemLogic;
use PHPUnit\Framework\TestCase;

class LineItemLogicTest extends TestCase
{
    public function testQuantity(): void
    {
        self::assertEquals('8.6666', LineItemLogic::getQuantity('8.6666', '', ''));
        self::assertEquals('8.6667', LineItemLogic::getQuantity('', '60', '520'));
        self::assertEquals('1.0000', LineItemLogic::getQuantity('', '', '520'));
    }

    public function testQuantityEmptyAmountThrowsException(): void
    {
        $this->expectExceptionMessage("Amount is required");
        $this->expectException(RuntimeException::class);
        LineItemLogic::getQuantity('', '', '');
    }
}
