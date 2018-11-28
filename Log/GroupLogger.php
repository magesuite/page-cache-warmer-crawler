<?php

namespace MageSuite\PageCacheWarmerCrawler\Log;
use Psr\Log\LoggerInterface;

/**
 * This logger will just forward all messages to upstream loggers.
 */
class GroupLogger extends \Psr\Log\AbstractLogger
{
    /**
     * @var LoggerInterface[]
     */
    private $upstreamLoggers;

    /**
     * @param LoggerInterface[] $upstreamLoggers
     */
    public function __construct(array $upstreamLoggers)
    {
        $this->upstreamLoggers = $upstreamLoggers;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        foreach ($this->upstreamLoggers as $upstreamLogger) {
            $upstreamLogger->log($level, $message, $context);
        }
    }
}