<?php
/**
 * @copyright Copyright (c) 1999-2017 netz98 GmbH (http://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

namespace N98\Magento\Command\Indexer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RecreateTriggersCommand
 * @package N98\Magento\Command\Indexer
 */
class RecreateTriggersCommand extends AbstractIndexerCommand
{
    /**
     * @var \Magento\Indexer\Model\Config
     */
    private $indexerCollection;

    protected function configure()
    {
        $this
            ->setName('index:trigger:recreate')
            ->setDescription('ReCreate all triggers');
    }

    /**
     * @param \Magento\Indexer\Model\Config $indexerCollection
     */
    public function inject(\Magento\Indexer\Model\Indexer\Collection $indexerCollection)
    {
        $this->indexerCollection = $indexerCollection;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if (!$this->initMagento()) {
            return;
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
    }
}
