<?php

namespace MageSuite\PageCacheWarmerCrawler\Log;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    const LOG_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    /**
     * @var string
     */
    protected $fileName = '/var/log/warmup-crawler.log';

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

        $this->setFormatter(new \Monolog\Formatter\LineFormatter(
            self::LOG_FORMAT,
            null,
            true,
            true
        ));
    }
}