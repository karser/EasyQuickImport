<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\QuickbooksCompany;
use App\Entity\QuickbooksCompanyRepositoryInterface;
use App\Event\QuickbooksServerSendRequestXmlEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCompanySubscriber implements EventSubscriberInterface
{
    private $companyRepo;
    private $em;

    public function __construct(QuickbooksCompanyRepositoryInterface $companyRepo,
                                EntityManagerInterface $em)
    {
        $this->companyRepo = $companyRepo;
        $this->em = $em;
    }

    public function onSendRequestXmlEvent(QuickbooksServerSendRequestXmlEvent $event): void
    {
        /** @var QuickbooksCompany|null $company */
        $company = $this->companyRepo->findOneBy(['qbUsername' => $event->getQbUsername()]);
        $xml = $event->getHookData()['strHCPResponse'] ?? null;
        if (null === $company || $xml === null || trim($xml) === '') {
            return;
        }
        try {
            $parser = new \QuickBooks_XML_Parser($xml);
        } catch (\Throwable $e) {
            return;
        }
        /** @var \QuickBooks_XML_Document|false $doc */
        $doc = $parser->parse($errnum, $errmsg);
        if ($doc === false) {
            return;
        }
        $company->setXml($xml);
        if (null !== $prefDto = $this->getPrefDto($doc->getRoot())) {
            $company->setMultiCurrencyEnabled($prefDto->getMultiCurrencyPreferencesIsMultiCurrencyOn());
            $symbol = $this->getDecimalSymbol($prefDto->getFinanceChargePreferencesAnnualInterestRate()) ?? $this->getDecimalSymbol($prefDto->getFinanceChargePreferencesMinFinanceCharge());
            $company->setDecimalSymbol($symbol);
            $company->setDigitGroupingSymbol($this->getDigitGroupingSymbol($symbol));
        }

        $companyFilename = $event->getHookData()['strCompanyFileName'] ?? null;
        if (null !== $companyFilename) {
            $company->setQbCompanyFile($companyFilename);
        }

        $this->em->flush();
    }

    public function getDecimalSymbol(?string $value): string
    {
        if (null !== $value) {
            $value = preg_replace('/[^,.]+/', '', $value);
            if (is_string($value) && $value !== '') {
                return $value[-1];
            }
        }
        return QuickbooksCompany::DEFAULT_DECIMAL_SYMBOL;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QuickbooksServerSendRequestXmlEvent::class => 'onSendRequestXmlEvent',
        ];
    }

    private function getDigitGroupingSymbol(string $decimalSymbol): string
    {
        return $decimalSymbol === ',' ? '.' :QuickbooksCompany::DEFAULT_DIGIT_GROUPING_SYMBOL;
    }

    private function getPrefDto(\QuickBooks_XML_Node $root): ?\QuickBooks_QBXML_Object_Preferences
    {
        $prefXml = $root->getChildAt('QBXML/QBXMLMsgsRs/PreferencesQueryRs/PreferencesRet');
        /** @var \QuickBooks_QBXML_Object_Preferences|false $prefDto */
        $prefDto = \QuickBooks_QBXML_Object::fromXML($prefXml, QUICKBOOKS_QUERY_PREFERENCES);
        return $prefDto instanceof \QuickBooks_QBXML_Object_Preferences ? $prefDto : null;
    }
}
