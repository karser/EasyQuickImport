<?php declare(strict_types=1);

namespace App\Command;

use App\User\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    private $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'The email of the user');
        $this->addOption('password', 'p',InputOption::VALUE_REQUIRED, 'The password of the user');
        $this->addOption('admin', null, InputOption::VALUE_NONE, 'Adds ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');
        $password = $input->getOption('password');
        Assert::string($password);
        $isAdmin = $input->getOption('admin');
        Assert::boolean($isAdmin);
        $roles = [$isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER'];
        $user = $this->userService->createUser($email, $password, $roles);

        $output->writeln('The user has been created with id '.$user->getId());

        return 0;
    }
}
