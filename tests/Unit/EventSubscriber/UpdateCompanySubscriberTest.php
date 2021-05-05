<?php declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\QuickbooksCompany;
use App\Entity\QuickbooksCompanyRepositoryInterface;
use App\Event\QuickbooksServerSendRequestXmlEvent;
use App\EventSubscriber\UpdateCompanySubscriber;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class UpdateCompanySubscriberTest extends TestCase
{
    private const USERNAME = 'test-name';

    /** @var MockObject&QuickbooksCompanyRepositoryInterface */
    private $companyRepo;
    /** @var MockObject&EntityManagerInterface */
    private $em;
    /** @var string */
    private $xml;
    /** @var UpdateCompanySubscriber */
    private $subscriber;

    public function setUp()
    {
        $this->companyRepo = $this->getMockBuilder(QuickbooksCompanyRepositoryInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->subscriber = new UpdateCompanySubscriber($this->companyRepo, $this->em);
        $this->xml = file_get_contents(__DIR__.'/../../Functional/fixtures/company_query_rs.xml');
        Assert::string($this->xml);
    }

    public function testOnSendRequestXmlEvent()
    {
        $this->em->expects(self::once())->method('flush');
        $this->companyRepo->expects(self::once())->method('findOneBy')
            ->willReturn($c = new QuickbooksCompany());

        $c->setMultiCurrencyEnabled(true);
        $c->setDecimalSymbol('.');
        $c->setDigitGroupingSymbol(',');
        $c->setQbCompanyFile(null);

        $this->subscriber->onSendRequestXmlEvent($this->getEvent($this->xml));
        Assert::false($c->isMultiCurrencyEnabled());
        Assert::same(',', $c->getDecimalSymbol());
        Assert::same('.', $c->getDigitGroupingSymbol());
        Assert::same('C:\\Users\\Public\\Documents\\Intuit\\QuickBooks\\Company Files\\Acme Inc.qbw', $c->getQbCompanyFile());
    }

    public function testDoesNothingIfNoCompany()
    {
        $this->em->expects(self::never())->method('flush');
        $this->companyRepo->expects(self::once())->method('findOneBy')->willReturn(null);

        $this->subscriber->onSendRequestXmlEvent($this->getEvent($this->xml));
    }

    public function testDoesNothingOnInvalidXML()
    {
        $this->em->expects(self::never())->method('flush');
        $this->companyRepo->expects(self::once())->method('findOneBy')
            ->willReturn($c = new QuickbooksCompany());

        $xml = 'invalid';
        $this->subscriber->onSendRequestXmlEvent($this->getEvent($xml));
    }

    public function testDoesNothingOnIncorrectXML()
    {
        $this->em->expects(self::never())->method('flush');
        $this->companyRepo->expects(self::once())->method('findOneBy')
            ->willReturn($c = new QuickbooksCompany());

        $xml = '<xml>';
        $this->subscriber->onSendRequestXmlEvent($this->getEvent($xml));
    }

    public function testGetDecimalSymbol()
    {
        self::assertSame('.', $this->subscriber->getDecimalSymbol('100,000.00'));
        self::assertSame(',', $this->subscriber->getDecimalSymbol('100.000,00'));
        self::assertSame(',', $this->subscriber->getDecimalSymbol('100,00'));
        self::assertSame('.', $this->subscriber->getDecimalSymbol('100.00'));
        self::assertSame('.', $this->subscriber->getDecimalSymbol('0'));
        self::assertSame('.', $this->subscriber->getDecimalSymbol(''));
        self::assertSame('.', $this->subscriber->getDecimalSymbol(null));
    }

    private function getEvent(?string $xml): QuickbooksServerSendRequestXmlEvent
    {
        \QuickBooks_WebConnector_Handlers::HOOK_AUTHENTICATE; //hack to load constants
        return new QuickbooksServerSendRequestXmlEvent(null, self::USERNAME, QUICKBOOKS_HANDLERS_HOOK_SENDREQUESTXML, '', [
            'username' => self::USERNAME,
            'ticket' => '20bdc17a-83aa-2de4-ad26-0614439c0391',
            'strHCPResponse' => $xml,
            'strCompanyFileName' => 'C:\\Users\\Public\\Documents\\Intuit\\QuickBooks\\Company Files\\Acme Inc.qbw',
            'qbXMLCountry' => 'US',
            'qbXMLMajorVers' => '13',
            'qbXMLMinorVers' => '0',
            'requestID' => null,
            'user' => self::USERNAME,
        ], []);
    }
}
