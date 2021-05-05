<?php declare(strict_types=1);

namespace App\Tests\Unit\SheetScheduler;

use App\SheetScheduler\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    const ADDR_1 = 'addr1';
    const ADDR_2 = 'addr2';
    const CITY = 'city';
    const STATE = 'state';
    const ZIP = 'zip';
    const COUNTRY = 'country';

    /** @var Customer */
    private $customer;

    public function setUp(): void
    {
        $this->customer = new Customer();
        $this->customer->setAddr1(self::ADDR_1);
        $this->customer->setAddr2(self::ADDR_2);
        $this->customer->setCity(self::CITY);
        $this->customer->setState(self::STATE);
        $this->customer->setPostalcode(self::ZIP);
        $this->customer->setCountry(self::COUNTRY);
        $this->customer->setCompanyName('');
        $this->customer->setFirstName('');
        $this->customer->setLastName('');
    }

    public function testWithoutCompanyAndName(): void
    {
        self::assertSame([
            self::ADDR_1,
            self::ADDR_2,
            '',
            '',
            '',
            self::CITY,
            self::STATE,
            '',
            self::ZIP,
            self::COUNTRY,
        ], $this->customer->composeBillAddress());
    }

    public function testWithCompanyAndName(): void
    {
        $this->customer->setCompanyName('Company');
        $this->customer->setFirstName('First');
        $this->customer->setLastName('Last');

        self::assertSame([
            'Attn: First Last',
            'Company',
            self::ADDR_1,
            self::ADDR_2,
            '',
            self::CITY,
            self::STATE,
            '',
            self::ZIP,
            self::COUNTRY,
        ], $this->customer->composeBillAddress());
    }

    public function testWithCompanyWithoutName(): void
    {
        $this->customer->setCompanyName('Company');

        self::assertSame([
            'Company',
            self::ADDR_1,
            self::ADDR_2,
            '',
            '',
            self::CITY,
            self::STATE,
            '',
            self::ZIP,
            self::COUNTRY,
        ], $this->customer->composeBillAddress());
    }

    public function testWithCompanyAndFirstName(): void
    {
        $this->customer->setCompanyName('Company');
        $this->customer->setFirstName('First');

        self::assertSame([
            'Attn: First',
            'Company',
            self::ADDR_1,
            self::ADDR_2,
            '',
            self::CITY,
            self::STATE,
            '',
            self::ZIP,
            self::COUNTRY,
        ], $this->customer->composeBillAddress());
    }

    public function testWithCompanyAndLastName(): void
    {
        $this->customer->setCompanyName('Company');
        $this->customer->setLastName('Last');

        self::assertSame([
            'Attn: Last',
            'Company',
            self::ADDR_1,
            self::ADDR_2,
            '',
            self::CITY,
            self::STATE,
            '',
            self::ZIP,
            self::COUNTRY,
        ], $this->customer->composeBillAddress());
    }
}
