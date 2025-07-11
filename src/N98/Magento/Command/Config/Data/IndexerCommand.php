<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Data;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Util\Console\Helper\TreeHelper;
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
            ->addOption('tree', 't', InputOption::VALUE_NONE, 'Show data as tree')
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

        if ($input->getOption('tree')) {
            $this->renderAsTree($data, $output);
        } else {
            $this->renderAsTable($data, $output, $input);
        }

        return Command::SUCCESS;
    }

    protected function renderAsTree(array $data, OutputInterface $output)
    {
        $tree = new TreeHelper();
        $tree->setTitle('Indexer Data Tree');

        foreach ($data as $row) {
            $node = $tree->newNode('<info>' . $row['title'] . '</info>');

            if (!empty($row['indexer_id'])) {
                $node->newNode(
                    sprintf(
                        '<info>indexer_id: </info><comment>%s</comment>',
                        $row['indexer_id']
                    )
                );
            }

            if (!empty($row['view_id'])) {
                $node->newNode(
                    sprintf(
                        '<info>view_id: </info><comment>%s</comment>',
                        $row['view_id']
                    )
                );
            }

            if (!empty($row['action_class'])) {
                $node->newNode(
                    sprintf(
                        '<info>action_class: </info><comment>%s</comment>',
                        $row['action_class']
                    )
                );
            }

            if (!empty($row['shared_index'])) {
                $node->newNode(
                    sprintf(
                        '<info>shared_index: </info><comment>%s</comment>',
                        $row['shared_index']
                    )
                );
            }

            if (count($row['dependencies']) > 0) {
                $dependenciesNode = $node->newNode('dependencies:');
                foreach ($row['dependencies'] as $dependency) {#
                    $dependenciesNode->addValue('<comment>' . $dependency . '</comment>');
                }
            }
        }

        $tree->printTree($output);
    }

    /**
     * @param array $data
     * @param OutputInterface $output
     * @param InputInterface $input
     * @return void
     */
    protected function renderAsTable(array $data, OutputInterface $output, InputInterface $input): void
    {
        $table = [];

        foreach ($data as $indexer) {
            $table[] = [
                'indexer_id' => $indexer['indexer_id'],
                'view_id' => $indexer['view_id'],
                'shared_index' => $indexer['shared_index'],
                'title' => $indexer['title'],
                'dependencies' => implode(',', $indexer['dependencies']),
                'action_class' => $indexer['action_class'],
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['indexer_id', 'view_id', 'shared_index', 'title', 'dependencies', 'action_class'])
            ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
