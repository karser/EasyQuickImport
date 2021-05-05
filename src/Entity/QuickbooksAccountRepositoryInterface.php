<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<QuickbooksAccount>
 */
interface QuickbooksAccountRepositoryInterface extends ObjectRepository
{
    public function deleteAll(QuickbooksCompany $company): int;

    public function getCurrency(string $username, string $accountName, ?string $accountType = null): ?string;

    public function findOneByName(string $username, string $accountName): ?QuickbooksAccount;
}
