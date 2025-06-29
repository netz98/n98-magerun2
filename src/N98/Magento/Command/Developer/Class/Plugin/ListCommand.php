<?php

namespace N98\Magento\Command\Developer\Class\Plugin;

use Magento\Developer\Model\Di\PluginList;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:class:plugin:list')
            ->setDescription('Lists plugins for a class name')
            ->addArgument('class', InputArgument::REQUIRED, 'Class name')
            ->addArgument('area', InputArgument::OPTIONAL, 'Area code (e.g. global, frontend, adminhtml)')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return 1;
        }

        /** @var PluginList $pluginList */
        $pluginList = $this->getObjectManager()->get(PluginList::class);
        $area = $input->getArgument('area') ?: 'global';
        $pluginList->setScopePriorityScheme([$area]);

        $class = ltrim($input->getArgument('class'), '\\');

        $config = $pluginList->getPluginsConfig();
        $methods = $pluginList->getPluginsListByClass($class);

        $meta = [];
        if (isset($config[$class])) {
            foreach ($config[$class] as $pluginName => $pluginData) {
                $instance = $pluginData['instance'] ?? '';
                $meta[$instance] = [
                    'name' => $pluginName,
                    'sortOrder' => $pluginData['sortOrder'] ?? 0,
                    'active' => empty($pluginData['disabled']) ? 1 : 0,
                ];
            }
        }

        $table = [];
        foreach ($methods as $type => $list) {
            foreach ($list as $instance => $methodList) {
                foreach ($methodList as $method) {
                    $info = $meta[$instance] ?? ['name' => '', 'sortOrder' => 0, 'active' => 1];
                    $table[] = [
                        $info['name'],
                        $instance,
                        $method,
                        $type,
                        $info['sortOrder'],
                        $info['active'],
                    ];
                }
            }
        }

        usort($table, function ($a, $b) {
            return $a[4] <=> $b[4];
        });

        if ($input->getOption('format') === null) {
            $this->writeSection($output, 'Plugins on: ' . $class);
        }

        $this->getHelper('table')
            ->setHeaders(['Plugin name', 'Plugin class', 'Method observed', 'Type', 'Order', 'Active'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return 0;
    }
}
