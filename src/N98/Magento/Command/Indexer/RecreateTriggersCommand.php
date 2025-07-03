<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Indexer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RecreateTriggersCommand
 * @package N98\Magento\Command\Indexer
 */
class RecreateTriggersCommand extends AbstractIndexerCommand
{
    protected function configure()
    {
        $this
            ->setName('index:trigger:recreate')
            ->setDescription('ReCreate all triggers');
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

        foreach ($this->getIndexerCollection() as $indexer) {
            /** @var $indexer \Magento\Framework\Indexer\IndexerInterface */
            if ($indexer->isScheduled()) {
                $indexer->getView()->unsubscribe();
                $indexer->getView()->subscribe();
                $output->writeln(
                    sprintf('Re-created triggers of indexer <info>%s</info>', $indexer->getTitle())
                );
            } else {
                $output->writeln(
                    sprintf('Skipped indexer <info>%s</info>. Mode must be "schedule".', $indexer->getTitle())
                );
            }
        }

        return Command::SUCCESS;
    }
}
