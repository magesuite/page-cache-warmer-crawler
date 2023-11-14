<?php

namespace MageSuite\PageCacheWarmerCrawler\Command;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCrawlWorkerCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \MageSuite\PageCacheWarmerCrawler\Service\CrawlWorkerFactory
     */
    private $crawlWorkerFactory;

    /**
     * @var \MageSuite\PageCacheWarmerCrawler\Service\ConfigurationProvider
     */
    private $configuration;

    public function __construct(
        \MageSuite\PageCacheWarmerCrawler\Log\Logger $logger,
        \MageSuite\PageCacheWarmerCrawler\Service\CrawlWorkerFactory $crawlWorkerFactory,
        \MageSuite\PageCacheWarmerCrawler\Service\ConfigurationProvider $configuration
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->crawlWorkerFactory = $crawlWorkerFactory;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cache:warm:pages-crawl-worker')
            ->setDescription('Executes worker which processes page cache warmup jobs')
            ->addOption('max-jobs', null, InputOption::VALUE_REQUIRED, 'Max number of jobs to be ran before terminating', 100)
            ->addOption('concurrency', null, InputOption::VALUE_REQUIRED, 'Max number of simulatenous warmup requests', null)
            ->addOption('varnish-uri', null, InputOption::VALUE_REQUIRED, 'Directly query varnish at this uri', null)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Size of single job batch', 10)
            ->addOption('min-runtime', null, InputOption::VALUE_REQUIRED, 'Miminum amount of time to stay up (working or waiting for jobs)', 30)
            ->addOption('min-runtime-delay', null, InputOption::VALUE_REQUIRED, 'Delay between job checks if `min-runtime` is not yet reached and there are no jobs', 5)
            ->addOption('log-requests', null, InputOption::VALUE_NONE, 'Whether to log all requests and responses for debugging')
            ->addOption('warmup-requests-timeout', null, InputOption::VALUE_REQUIRED, 'Connection timeout for warmup requests', 60)
            ->addOption('session-requests-timeout', null, InputOption::VALUE_REQUIRED, 'Connection timeout for log in related requests', 30)
            ->addOption('accept-encoding', null, InputOption::VALUE_REQUIRED, 'Value of Accept-Encoding header for warmup requests', 'gzip, deflate')
            ->addOption('target-ttfb', null, InputOption::VALUE_REQUIRED, 'Target TTFB (in seconds) to keep below or start throttling', null)
            ->addOption('disable-throttling', null, InputOption::VALUE_NONE, 'Disable throttling entirely')
            ->addOption('retry-threshold', null, InputOption::VALUE_REQUIRED, 'How long to wait before retrying unfinished job', '15 minutes')
        ;
    }

    private function createLogger(
        \Symfony\Component\Console\Output\OutputInterface $output
    ): \Psr\Log\LoggerInterface {
        /* We want to log both to magento log and to the console's output.
         * We cannot create this logger in di.xml because we need the OutputInterface for this. */
        return new \MageSuite\PageCacheWarmerCrawler\Log\GroupLogger([
            $this->logger,
            new \MageSuite\PageCacheWarmerCrawler\Log\ConsoleLogger($output)
        ]);
    }

    private function resolveSettings(
        \Symfony\Component\Console\Input\InputInterface $input
    ): array {
        return array_merge([
            'max_jobs' => intval($input->getOption('max-jobs')),
            'concurrency' => $input->getOption('concurrency') !== null ? intval($input->getOption('concurrency')) : $this->configuration->getDefaultConcurrency(),
            'varnish_uri' => $input->getOption('varnish-uri') !== null ? $input->getOption('varnish-uri') : $this->configuration->getVarnishUri(),
            'batch_size' => intval($input->getOption('batch-size')),
            'min_runtime' => floatval($input->getOption('min-runtime')),
            'min_runtime_delay' => floatval($input->getOption('min-runtime-delay')),
            'log_requests' => !!$input->getOption('log-requests'),
            'warmup_requests_timeout' => intval($input->getOption('warmup-requests-timeout')),
            'session_requests_timeout' => intval($input->getOption('session-requests-timeout')),
            'session_storage_dir' => $this->configuration->getSessionStorageDirectory(),
            'throttle' => !!$input->getOption('disable-throttling') ? false : [
                'target_ttfb' => $input->getOption('target-ttfb') !== null ? intval($input->getOption('target-ttfb')) : $this->configuration->getDefaultTargetTTFB(),
            ],
            'warmup_headers' => [
                'X-Warmup' => 'yes',
                'Accept-Encoding' => $input->getOption('accept-encoding')
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $worker = $this->crawlWorkerFactory->createWorker(
            $this->createLogger($output)
        );

        $worker->work($this->resolveSettings($input));
    }
}
