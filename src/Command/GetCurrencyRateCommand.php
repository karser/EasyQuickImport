<?php declare(strict_types=1);

namespace App\Command;

use App\Currency\CurrencyExchangerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class GetCurrencyRateCommand extends Command
{
    protected static $defaultName = 'app:currency:get';

    private $currencyExchanger;

    public function __construct(CurrencyExchangerInterface $currencyExchanger)
    {
        parent::__construct();
        $this->currencyExchanger = $currencyExchanger;
    }

    protected function configure(): void
    {
        $this->addArgument('target', InputArgument::REQUIRED, '');

        $this->addOption('base', null,InputOption::VALUE_REQUIRED, 'HKD', 'HKD');
        $this->addOption('date', null,InputOption::VALUE_REQUIRED, 'Y-m-d', date('Y-m-d'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $input->getArgument('target');
        Assert::string($target);

        $base = $input->getOption('base');
        Assert::string($base);

        $date = $input->getOption('date');
        Assert::string($date);

        $rate = $this->currencyExchanger->getExchangeRate($base, $target, $date);
        $output->writeln(sprintf('%.10f', $rate));

        return 0;

    }
}
