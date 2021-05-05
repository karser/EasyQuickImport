<?php declare(strict_types=1);

namespace App\Accounts;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksAccountRepositoryInterface;
use App\Entity\QuickbooksCompany;
use App\Exception\RuntimeException;
use App\QuickbooksFormatter;
use App\QuickbooksServerInterface;
use Doctrine\ORM\EntityManagerInterface;
use QuickBooks_QBXML_Object;
use QuickBooks_QBXML_Object_Account;
use QuickBooks_XML_Document;
use Webmozart\Assert\Assert;

class AccountsUpdater
{
    const ACCOUNTS_UPDATE_REQUEST_ID = '33a768f8-d6d8-48e9-8675-58089ca76273';

    private $quickbooksFormatter;
    private $quickbooksServer;
    private $em;

    public function __construct(QuickbooksFormatter $quickbooksFormatter,
                                QuickbooksServerInterface $quickbooksServer,
                                EntityManagerInterface $em
    ) {
        $this->quickbooksFormatter = $quickbooksFormatter;
        $this->quickbooksServer = $quickbooksServer;
        $this->em = $em;
    }

    public function scheduleUpdate(string $username): bool
    {
        $account = new QuickBooks_QBXML_Object_Account();

        $queryXml = $this->quickbooksFormatter->formatForOutput($account->asQBXML(QUICKBOOKS_QUERY_ACCOUNT));
        return $this->quickbooksServer->schedule($username, QUICKBOOKS_QUERY_ACCOUNT, self::ACCOUNTS_UPDATE_REQUEST_ID, $queryXml);
    }

    public function update(string $qbUsername, string $xml): void
    {
        $parser = new \QuickBooks_XML_Parser($xml);
        /** @var QuickBooks_XML_Document|false $doc */
        $doc = $parser->parse($errnum, $errmsg);
        if ($doc === false) {
            throw new RuntimeException("Unable to parse AccountsXML. {$errnum}:{$errmsg}");
        }

        $companyRepo = $this->em->getRepository(QuickbooksCompany::class);
        $company = $companyRepo->findOneBy(['qbUsername' => $qbUsername]);
        Assert::notNull($company);
        $user = $company->getUser();
        Assert::notNull($user);

        /** @var QuickbooksAccountRepositoryInterface $repo */
        $repo = $this->em->getRepository(QuickbooksAccount::class);
        $repo->deleteAll($company);

        $root = $doc->getRoot();
        $List = $root->getChildAt('QBXML/QBXMLMsgsRs/AccountQueryRs');
        foreach ($List->children() as $child) {
            $accountXml = $child->getChildAt('AccountRet');
            /** @var QuickBooks_QBXML_Object_Account|false $dto */
            $dto = QuickBooks_QBXML_Object::fromXML($accountXml, QUICKBOOKS_QUERY_ACCOUNT);
            Assert::isInstanceOf($dto, QuickBooks_QBXML_Object_Account::class);
            $entity = $this->fromDto($dto);
            $entity->setUser($user);
            $entity->setCompany($company);
            $this->em->persist($entity);
        }
        $this->em->flush();
    }

    private function fromDto(QuickBooks_QBXML_Object_Account $dto): QuickbooksAccount
    {
        $entity = new QuickbooksAccount();
        $entity->setFullName($dto->getFullName());
        $entity->setCurrency($dto->get('CurrencyRef FullName') ?? 'US Dollar');
        $entity->setAccountType($dto->getAccountType());
        $entity->setSpecialAccountType($dto->getSpecialAccountType());
        $entity->setAccountNumber($dto->getAccountNumber());
        return $entity;
    }
}
