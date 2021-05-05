<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\QuickbooksCompany;
use App\Entity\QuickbooksCompanyRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuickbooksCompanyRepository extends ServiceEntityRepository implements QuickbooksCompanyRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuickbooksCompany::class);
    }
}
