<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FlushCommand
 * @package N98\Magento\Command\Cache
 */
class FlushCommand extends AbstractModifierCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:flush')
            ->addArgument('type', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Cache type code like "config"')
            ->setDescription('Flush magento cache storage');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $cacheManager = $this->getCacheManager();

        /* @var $eventManager \Magento\Framework\Event\ManagerInterface */
        $eventManager = $this->getObjectManager()->get('Magento\Framework\Event\ManagerInterface');
        $eventManager->dispatch('adminhtml_cache_flush_all');

        $typesToClean = $input->getArgument('type');

        $availableTypes = $cacheManager->getAvailableTypes();
        foreach ($availableTypes as $cacheType) {
            if (count($typesToClean) == 0 || in_array($cacheType, $typesToClean)) {
                $cacheManager->flush([$cacheType]);
                $output->writeln('<info><comment>' . $cacheType . '</comment> cache flushed</info>');
            }
        }

        return Command::SUCCESS;
    }
}
