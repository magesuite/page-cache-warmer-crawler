<?php

namespace MageSuite\PageCacheWarmerCrawler\Log;

use Magento\Framework\Logger\Monolog;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    const LOG_FORMAT = "[%datetime%] %level_name%: %message% %context% %extra%\n";

    /**
     * @var string
     */
    protected $fileName = '/var/log/cache_warmer_crawler.log';

    /**
     * @var int
     */
    protected $loggerType = \Psr\Log\LogLevel::DEBUG;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null
    ) {
        parent::__construct($filesystem, $filePath, $fileName);

        /* Log from info, do not log debug stuff */
        $this->setLevel(\Monolog\Logger::INFO);

        $this->setFormatter(new \Monolog\Formatter\LineFormatter(
            self::LOG_FORMAT,
            null,
            true,
            true
        ));
    }
}
