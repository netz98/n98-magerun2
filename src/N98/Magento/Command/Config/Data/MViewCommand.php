<?php

namespace N98\Magento\Command\Config\Data;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Util\Console\Helper\TreeHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MViewCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Mview\Config\Reader
     */
    private $mviewConfigReader;

    protected function configure()
    {
        $this
            ->setName('config:data:mview')
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
            ->setDescription('Dump merged data of mview.xml files');
    }

    /**
     * @param \Magento\Framework\Mview\Config\Reader $mviewConfigReader
     */
    public function inject(\Magento\Framework\Mview\Config\Reader $mviewConfigReader)
    {
        $this->mviewConfigReader = $mviewConfigReader;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->mviewConfigReader->read();

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
        $tree->setTitle('MView Data Tree');

        foreach ($data as $row) {
            $node = $tree->newNode('<info>' . $row['view_id'] . '</info>');

            if (!empty($row['group'])) {
                $node->newNode(
                    sprintf(
                        '<info>group: </info><comment>%s</comment>',
                        $row['group']
                    )
                );
            }

            if (!empty($row['walker'])) {
                $node->newNode(
                    sprintf(
                        '<info>walker: </info><comment>%s</comment>',
                        $row['walker']
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

            if (count($row['subscriptions']) > 0) {
                $dependenciesNode = $node->newNode('subscriptions:');
                foreach ($row['subscriptions'] as $subscriptionKey => $subscriptionData) {
                    $subscriptionNode = $dependenciesNode->newNode('<comment>' . $subscriptionKey . '</comment>');
                    $subscriptionNode
                        ->newNode('<info>column: </info><comment>' . $subscriptionData['column'] . '</comment>');
                    if (!empty($subscriptionData['subscription_model'])) {
                        $subscriptionNode
                            ->newNode(
                                sprintf(
                                    '<info>subscription_model:</info><comment>%s</comment>',
                                    $subscriptionData['subscription_model']
                                )
                            );
                    }

                    if (!empty($subscriptionData['additional_columns'])) {
                        $subscriptionNode
                            ->newNode('additional_columns:')
                            ->addValue('<comment>' . implode(',', $subscriptionData['additional_columns']) . '</comment>');
                    }
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

        foreach ($data as $row) {
            $table[] = [
                'view_id' => $row['view_id'],
                'group' => $row['group'],
                'shared_index' => $row['walker'],
                'action_class' => $row['action_class'],
                'subscriptions' => $this->formatSubscriptions($row['subscriptions']),
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['view_id', 'group', 'walker', 'action_class', 'subscriptions'])
            ->renderByFormat($output, $table, $input->getOption('format'));
    }

    /**
     * @param array $subscriptions
     * @return string
     */
    private function formatSubscriptions(array $subscriptions)
    {
        $return = [];

        foreach ($subscriptions as $subscription) {
            $return[] = sprintf('%s(%s)', $subscription['name'], $subscription['column']);
        }

        return implode(',', $return);
    }
}
