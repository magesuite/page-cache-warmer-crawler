<?php

namespace MageSuite\PageCacheWarmerCrawler\Service;

use Magento\Framework\Filesystem\DirectoryList;

class ConfigurationProvider
{
    const CONFIG_PATH_CUSTOMER_PASSWORD = 'cache_warmer/general/password';
    const CONFIG_PATH_CUSTOMER_DOMAIN = 'cache_warmer/general/domain';

    const CONFIG_PATH_DEFAULT_CONCURRENCY = 'cache_warmer_crawler/general/default_concurrency';
    const CONFIG_PATH_VARNISH_URI = 'cache_warmer_crawler/general/varnish_uri';
    const CONFIG_PATH_STORAGE_DIR = 'cache_warmer_crawler/general/storage_dir';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->directoryList = $directoryList;
    }

    public function getCustomerAccountPassword(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMER_PASSWORD);
    }

    public function getCustomerDomain(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CUSTOMER_DOMAIN);
    }

    public function getDatabaseHost(): string
    {
        return $this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
            '/' . \Magento\Framework\Config\ConfigOptionsListConstants::KEY_HOST
        );
    }

    public function getDatabaseName(): string
    {
        return $this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
            '/' . \Magento\Framework\Config\ConfigOptionsListConstants::KEY_NAME
        );
    }

    public function getDatabaseUser(): string
    {
        return $this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
            '/' . \Magento\Framework\Config\ConfigOptionsListConstants::KEY_USER
        );
    }

    public function getDatabasePass(): string
    {
        return $this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
            '/' . \Magento\Framework\Config\ConfigOptionsListConstants::KEY_PASSWORD
        );
    }

    public function getSessionStorageDirectory(): string
    {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) .
            '/' . $this->scopeConfig->getValue(self::CONFIG_PATH_STORAGE_DIR);
    }

    public function getVarnishUri(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_VARNISH_URI);
    }

    public function getDefaultConcurrency(): int
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_DEFAULT_CONCURRENCY);
    }

}