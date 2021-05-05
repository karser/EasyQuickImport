<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksAccountRepositoryInterface;
use App\Entity\QuickbooksCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuickbooksAccountRepository extends ServiceEntityRepository implements QuickbooksAccountRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuickbooksAccount::class);
    }

    public function deleteAll(QuickbooksCompany $company): int
    {
        $query = $this->createQueryBuilder('a')
            ->delete()
            ->where('a.company = :company')
            ->setParameter('company', $company)
            ->getQuery();

        /** @var int $deleted */
        $deleted = $query->execute();
        return $deleted;
    }

    public function findOneByName(string $username, string $accountName): ?QuickbooksAccount
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.fullName = :fullName')
            ->setParameter('fullName', $accountName)
            ->join('a.company', 'c')
            ->andWhere('c.qbUsername = :qbUsername')
            ->setParameter('qbUsername', $username)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof QuickbooksAccount ? $result : null;
    }

    public function getCurrency(string $username, string $accountName, ?string $accountType = null): ?string
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('a.currency')
            ->from($this->_entityName, 'a')
            ->setMaxResults(1)
            ->join('a.company', 'c')
            ->where('c.qbUsername = :username')
            ->setParameter('username', $username)
            ->andWhere('a.fullName = :fullName')
            ->setParameter('fullName', $accountName);
        if (null !== $accountType) {
            $qb->andWhere('a.accountType = :accountType')
                ->setParameter('accountType', $accountType);
        }
        $res = $qb->getQuery()->getArrayResult();
        if (count($res) === 0) {
            return null;
        }
        return $res[0]['currency'];
    }
}
