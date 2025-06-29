<?php

namespace N98\Magento\Command\Developer\Di\Plugin;

use Exception;
use Magento\Developer\Model\Di\PluginList;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
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
            ->setName('dev:di:plugin:list')
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

        // Load plugin names from di.xml files
        $diXmlPluginNames = $this->loadPluginNamesFromDiXml($class, $area);

        $meta = [];
        if (isset($config[$class])) {
            foreach ($config[$class] as $pluginName => $pluginData) {
                $instance = $pluginData['instance'] ?? '';
                $meta[$instance] = [
                    'name' => $pluginName, // This is the name attribute from di.xml
                    'sortOrder' => $pluginData['sortOrder'] ?? 0,
                    'active' => empty($pluginData['disabled']) ? 1 : 0,
                ];
            }
        }

        $table = [];
        foreach ($methods as $type => $list) {
            foreach ($list as $instance => $methodList) {
                foreach ($methodList as $method) {
                    // Get plugin metadata if available, otherwise create default values
                    $info = $meta[$instance] ?? ['name' => '', 'sortOrder' => 0, 'active' => 1];

                    // Try to get plugin name from di.xml files if available
                    if (empty($info['name']) && isset($diXmlPluginNames[$instance])) {
                        $info['name'] = $diXmlPluginNames[$instance];
                    }

                    // If plugin name is still empty, use class name as fallback
                    if (empty($info['name'])) {
                        $info['name'] = $this->getShortClassName($instance);
                    }

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

    /**
     * Get the short class name from a fully qualified class name
     *
     * @param string $className
     * @return string
     */
    protected function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Load plugin configuration using Magento's public interfaces
     *
     * @param string $className The class name to find plugins for
     * @param string $area Area code (e.g., frontend, adminhtml, etc.)
     * @return array Array mapping plugin class names to their di.xml plugin names
     */
    protected function loadPluginNamesFromDiXml(string $className, string $area = 'global'): array
    {
        $objectManager = $this->getObjectManager();
        $pluginNameMap = [];

        try {
            // Use the Developer module's PluginList which is specifically designed to get plugin information
            /** @var \Magento\Developer\Model\Di\PluginList $pluginList */
            $pluginList = $objectManager->get(PluginList::class);
            $pluginList->setScopePriorityScheme([$area]);

            // Get plugin configurations using public methods
            $config = $pluginList->getPluginsConfig();

            // Process the plugin configuration
            if (isset($config[$className])) {
                foreach ($config[$className] as $pluginName => $pluginData) {
                    if (isset($pluginData['instance'])) {
                        $pluginNameMap[$pluginData['instance']] = $pluginName;
                    }
                }
            }

            // Try to get additional plugin information from ConfigLoaderInterface
            /** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
            $configLoader = $objectManager->get(ConfigLoaderInterface::class);
            $diConfig = $configLoader->load($area);

            // Check for preferences that might be used as plugins
            if (isset($diConfig['preferences'])) {
                foreach ($diConfig['preferences'] as $interfaceName => $implementation) {
                    // If this is a plugin for our class or if it's related
                    if (strpos($interfaceName, '\\Plugin\\') !== false ||
                        strpos($implementation, '\\Plugin\\') !== false) {

                        // Extract potential plugin name from the class/interface name
                        $potentialPluginName = $this->getShortClassName($implementation);

                        // Only add if we don't already have a name for this implementation
                        if (!isset($pluginNameMap[$implementation])) {
                            $pluginNameMap[$implementation] = $potentialPluginName;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // If there's an error, we'll return what we have so far
        }

        return $pluginNameMap;
    }
}
