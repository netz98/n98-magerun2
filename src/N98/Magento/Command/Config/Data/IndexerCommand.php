<?php

namespace N98\Magento\Command\Config\Data;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexerCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Indexer\Config\Reader
     */
    private $indexConfigReader;

    protected function configure()
    {
        $this
            ->setName('config:data:indexer')
            ->addOption(
                'scope',
                's',
                InputOption::VALUE_OPTIONAL,
                'Config scope (global, adminhtml, frontend, graphql, webapi_rest, webapi_soap, ...)',
                'global'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('Dump merged data of indexer.xml files');
    }

    /**
     * @param \Magento\Framework\Indexer\Config\Reader $indexerConfigReader
     */
    public function inject(\Magento\Framework\Indexer\Config\Reader $indexerConfigReader)
    {
        $this->indexConfigReader = $indexerConfigReader;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->indexConfigReader->read();

        $table = [];

        foreach ($data as $indexer) {
            $table[] = [
                'indexer_id' => $indexer['indexer_id'],
                'view_id' => $indexer['view_id'],
                'shared_index' => $indexer['shared_index'],
                'title' => $indexer['title'],
                'dependencies' => implode(',', $indexer['dependencies']),
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['indexer_id', 'view_id', 'shared_index', 'title', 'dependencies'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
