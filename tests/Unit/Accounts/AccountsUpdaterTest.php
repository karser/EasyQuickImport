<?php declare(strict_types=1);

namespace App\Tests\Unit\Accounts;

use App\Accounts\AccountsUpdater;
use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksAccountRepositoryInterface;
use App\Entity\QuickbooksCompany;
use App\Entity\QuickbooksCompanyRepositoryInterface;
use App\Entity\User;
use App\QuickbooksFormatter;
use App\QuickbooksServerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccountsUpdaterTest extends TestCase
{
    /** @var AccountsUpdater */
    private $updater;
    /** @var EntityManagerInterface&MockObject */
    private $em;
    /** @var QuickbooksAccountRepositoryInterface&MockObject */
    private $accountRepo;
    /** @var QuickbooksCompanyRepositoryInterface&MockObject */
    private $companyRepo;
    /** @var QuickbooksServerInterface|MockObject */
    private $server;

    public function setUp(): void
    {
        $this->server = $this->getMockBuilder(QuickbooksServerInterface::class)->getMock();
        $this->accountRepo = $this->getMockBuilder(QuickbooksAccountRepositoryInterface::class)->getMock();
        $this->companyRepo = $this->getMockBuilder(QuickbooksCompanyRepositoryInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->em->method('getRepository')->willReturnMap([
            [QuickbooksAccount::class, $this->accountRepo],
            [QuickbooksCompany::class, $this->companyRepo],
        ]);


        $this->updater = new AccountsUpdater(
            new QuickbooksFormatter(),
            $this->server,
            $this->em
        );
    }


    public function testSchedule(): void
    {
        $this->server->expects(self::once())->method('schedule')
            ->with('user_test', QUICKBOOKS_QUERY_ACCOUNT, AccountsUpdater::ACCOUNTS_UPDATE_REQUEST_ID)
            ->willReturn(true);
        self::assertTrue($this->updater->scheduleUpdate('user_test'));
    }

    public function testUpdate(): void
    {
        $this->accountRepo->expects(self::once())->method('deleteAll');

        $user = new User();
        $user->setId(999);
        $company = new QuickbooksCompany('test_user');
        $company->setUser($user);
        $this->companyRepo->method('findOneBy')->willReturn($company);

        $this->em->expects(self::atLeastOnce())->method('persist')
            ->with(self::isInstanceOf(QuickbooksAccount::class));
        $this->em->expects(self::once())->method('flush');

        $xml = file_get_contents(__DIR__.'/chart_of_accounts.xml');
        $this->updater->update('test_user', $xml);
    }
}
