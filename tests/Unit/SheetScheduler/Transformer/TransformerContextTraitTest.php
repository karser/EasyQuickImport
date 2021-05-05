<?php declare(strict_types=1);

namespace App\Tests\Unit\SheetScheduler\Transformer;

use App\Entity\QuickbooksCompany;
use App\SheetScheduler\Transformer\TransformerContextTrait;
use PHPUnit\Framework\TestCase;

class TransformerContextTraitTest extends TestCase
{
    use TransformerContextTrait;

    /** @var QuickbooksCompany|null */
    private $company;

    public function setUp(): void
    {
        $this->company = new QuickbooksCompany('test');
    }

    public function testDefaultMultiCurrencyDisabled()
    {
        self::assertFalse($this->isMultiCurrencyEnabled());
    }

    public function testMultiCurrencyEnabled()
    {
        $this->company->setMultiCurrencyEnabled(true);
        self::assertTrue($this->isMultiCurrencyEnabled());
    }

    public function testNoCompanyMultiCurrencyDisabled()
    {
        $this->company = null;
        self::assertFalse($this->isMultiCurrencyEnabled());
    }

    public function testDefaultUsedDecimalSymbolDot()
    {
        self::assertSame('100.00', $this->getAmount('100.00'));
        self::assertSame('0.00',$this->getAmount('0.00'));
        self::assertSame('0', $this->getAmount('0'));
    }

    public function testUsedDecimalSymbolComma()
    {
        $this->company->setDecimalSymbol(',');

        self::assertSame('100,00', $this->getAmount('100.00'));
        self::assertSame('0,00',$this->getAmount('0.00'));
        self::assertSame('0', $this->getAmount('0'));
    }
}
