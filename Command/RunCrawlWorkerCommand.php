<?php

namespace MageSuite\PageCacheWarmerCrawler\Command;

class RunCrawlWorkerCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \MageSuite\PageCacheWarmerCrawler\Log\Logger $logger
    ) {
        parent::__construct();

        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('magesuite:page-cache:crawl-worker')
            ->setDescription('Warms node cache and optionally clears cache if new code is detected. This command shall be ran when new app node is added as the first thing on it.')
            ->addOption('force', 'f', \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Force even if already warm')
            ->addOption('local-url', 'u', \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Url of the local app instance', 'http://localhost:80')
        ;
    }

    private function createLogger(\Symfony\Component\Console\Output\OutputInterface $output): \Psr\Log\LoggerInterface
    {
        /* We want to log both to magento log and to the console's output.
         * We cannot create this logger in di.xml because we need the OutputInterface for this. */
        return new \MageSuite\PageCacheWarmerCrawler\Log\GroupLogger([
            $this->logger,
            new \Symfony\Component\Console\Logger\ConsoleLogger($output)
        ]);
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $logger = $this->createLogger($output);

        $queue = new \MageSuite\PageCacheWarmerCrawlWorker\Queue\DatabaseQueue([
            'dbname' => 'magesuite',
            'user' => 'root',
            'password' => 'vagrant',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ]);

        $credentials = new \MageSuite\PageCacheWarmerCrawlWorker\Customer\PreconfiguredCredentialsProvider('p4ssw0rd', 'creativeshop.me');

        $worker = new \MageSuite\PageCacheWarmerCrawlWorker\Worker($credentials, $queue, $logger);
        $worker->work(1, 100, 2, 'http://127.0.0.1:8080', true);
    }
}