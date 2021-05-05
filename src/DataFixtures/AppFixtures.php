<?php

namespace App\DataFixtures;

use App\Entity\QuickbooksCompany;
use App\User\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->userService->createUser('user@example.com', 'pass123');

        $company = new QuickbooksCompany('3607a3e9-ee11-4787-bd43-cdcb048038ad');
        $company->setQbCompanyFile('C:\\Users\\Public\\Documents\\Intuit\\QuickBooks\\Company Files\\Acme Inc.qbw');
        $company->setCompanyName('Acme Inc');
        $company->setQbPassword('Ohkq3AO');
        $company->setUser($user);
        $manager->persist($company);

        $manager->flush();
    }
}
