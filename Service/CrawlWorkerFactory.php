<?php

namespace MageSuite\PageCacheWarmerCrawler\Service;


class CrawlWorkerFactory
{
    /**
     * @var \MageSuite\PageCacheWarmerCrawler\Service\ConfigurationProvider
     */
    private $configuration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \MageSuite\PageCacheWarmerCrawler\Service\ConfigurationProvider $configuration,
        \MageSuite\PageCacheWarmerCrawler\Log\Logger $logger
    ) {
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    private function createCredentialsProvider(): \MageSuite\PageCacheWarmerCrawlWorker\Customer\CredentialsProvider
    {
        return new \MageSuite\PageCacheWarmerCrawlWorker\Customer\PreconfiguredCredentialsProvider(
            $this->configuration->getCustomerAccountPassword(),
            $this->configuration->getCustomerDomain()
        );
    }

    private function createQueue(): \MageSuite\PageCacheWarmerCrawlWorker\Queue\Queue
    {
        return new \MageSuite\PageCacheWarmerCrawlWorker\Queue\DatabaseQueue([
            'dbname' => $this->configuration->getDatabaseName(),
            'user' => $this->configuration->getDatabaseUser(),
            'password' => $this->configuration->getDatabasePass(),
            'host' => $this->configuration->getDatabaseHost(),
            'driver' => 'pdo_mysql',
        ]);
    }

    public function createWorker(\Psr\Log\LoggerInterface $logger = null): \MageSuite\PageCacheWarmerCrawlWorker\Worker
    {
        return new \MageSuite\PageCacheWarmerCrawlWorker\Worker(
            $this->createCredentialsProvider(),
            $this->createQueue(),
            null !== $logger ? $logger : $this->logger
        );
    }
}