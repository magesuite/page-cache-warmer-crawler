<?php

namespace MageSuite\PageCacheWarmerCrawler\Command;

class RunCrawlWorkerCommand extends \Symfony\Component\Console\Command\Command
{
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

    /**
     * {@inheritdoc}
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
    }
}