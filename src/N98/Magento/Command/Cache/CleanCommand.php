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

class CleanCommand extends AbstractModifierCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:clean')
            ->addArgument('type', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Cache type code like "config"')
            ->setDescription('Clean magento cache');

        $help = <<<HELP
Cleans expired cache entries.

If you would like to clean only one cache type use like:

   $ n98-magerun2.phar cache:clean full_page

If you would like to clean multiple cache types at once use like:

   $ n98-magerun2.phar cache:clean full_page block_html

If you would like to remove all cache entries use `cache:flush`

HELP;
        $this->setHelp($help);
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
        $eventManager = $this->getObjectManager()->get('\Magento\Framework\Event\ManagerInterface');
        $availableTypes = $cacheManager->getAvailableTypes();

        $typesToClean = $input->getArgument('type');

        if (!empty($typesToClean)) {
            $validTypesToClean = [];
            foreach ($typesToClean as $index => $type) {
                if (in_array($type, $availableTypes)) {
                    $validTypesToClean[] = $type;
                } else {
                    unset($typesToClean[$index]);
                    $output->writeln('<info><comment>"' . $type . '"</comment> skipped (unknown cache type)</info>');
                }
            }
            if (empty($validTypesToClean)) {
                $output->writeln('<error>Aborting clean</error>');
                return Command::FAILURE;
            }
        }

        foreach ($availableTypes as $type) {
            if (count($typesToClean) == 0 || in_array($type, $typesToClean)) {
                $cacheManager->clean([$type]);
                $eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => $type]);
                $output->writeln('<info><comment>' . $type . '</comment> cache cleaned</info>');
            }
        }

        return Command::SUCCESS;
    }
}
