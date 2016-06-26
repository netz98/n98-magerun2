<?php

namespace N98\Magento\Command\Cache;

use N98\Magento\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlushCommand extends AbstractModifierCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:flush')
            ->setDescription('Flush magento cache storage')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $cacheManager = $this->getCacheManager();

        $availableTypes = $cacheManager->getAvailableTypes();
        foreach ($availableTypes as $cacheType) {
            $cacheManager->flush(array($cacheType));
            $output->writeln('<info><comment>' . $cacheType . '</comment> cache flushed</info>');
        }
    }
}
