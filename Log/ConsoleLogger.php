<?php

namespace MageSuite\PageCacheWarmerCrawler\Log;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends \Psr\Log\AbstractLogger implements \Psr\Log\LoggerInterface
{
    /* Max length of level name */
    const PADDING = 10;

    const VERBOSITY_LEVEL_MAP = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL  => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING   => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE    => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO      => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::DEBUG     => OutputInterface::VERBOSITY_VERY_VERBOSE,
    ];

    const LEVEL_COLOR_MAP = [
        LogLevel::EMERGENCY => 'red',
        LogLevel::ALERT     => 'red',
        LogLevel::CRITICAL  => 'red',
        LogLevel::ERROR     => 'red',
        LogLevel::WARNING   => 'yellow',
        LogLevel::NOTICE    => 'cyan',
        LogLevel::INFO      => 'blue',
        LogLevel::DEBUG     => 'default',
    ];
    
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        OutputInterface $output
    ) {
        $this->output = $output;
    }

    public function log($level, $message, array $context = [])
    {
        $output = $this->output;

        if ($this->output instanceof ConsoleOutput && in_array($level, [
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
        ])) {
            $output = $this->output->getErrorOutput();
        }

        if ($output->getVerbosity() >= self::VERBOSITY_LEVEL_MAP[$level]) {
            $this->writeItem($output, $level, $message, $context);
        }
    }

    protected function writeItem(OutputInterface $output, string $level, string $message, array $context)
    {
        $padding = self::PADDING;
        $lines = explode("\n", $message);

        $message = $lines[0];
        $details =
            array_map(
                function ($line) use ($padding) {
                    return str_repeat(' ', $padding) . $line;
                },
                array_slice($lines, 1)
            );

        if (!empty($details)) {
            $message .= "\n" . implode("\n", $details);
        }

        $message = preg_replace_callback_array([
            '~(:\s\"?)([^,\n$]+)(\"?|\n)~' => function($m) {
                return $m[1] . '<fg=white>' . $m[2] . '</>' . $m[3];
            },
            '~\[[A-Z-_0-9:]+\]~' => function($m) {
                return '<fg=magenta>' . $m[0] . '</>';
            },
        ], $message);

        $textColor = self::LEVEL_COLOR_MAP[$level];
        $message = sprintf('<fg=%s>[%s]</>%s%s',
            $textColor,
            $level,
            str_repeat(' ', $padding - strlen($level) - 2),
            $message
        );

        $output->write("$message\n");
    }
}