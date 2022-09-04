<?php

namespace N98\Magento\Command\Route;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\Route\Config;
use Magento\Framework\Module\Dir\Reader;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $reader;

    /**
     * @var \Magento\Framework\App\Route\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    private $areaList;

    /**
     * @var \Magento\Framework\App\Route\Config\Reader
     */
    private $configReader;

    protected function configure()
    {
        $this
            ->setName('route:list')
            ->setDescription('Lists all registered routes')
            ->addOption(
                'area',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Route area code. One of [frontend,adminhtml]'
            )->addOption(
                'module',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Show registered routes of a module'
            )->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param Reader $reader
     * @param Config $config
     * @param AreaList $areaList
     * @param Config\Reader $configReader
     * @return void
     */
    public function inject(
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\App\Route\Config $config,
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\App\Route\Config\Reader $configReader
    ) {
        $this->reader = $reader;
        $this->config = $config;
        $this->areaList = $areaList;
        $this->configReader = $configReader;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = [];
        $moduleActions = [];

        $actionPaths = $this->reader->getActionFiles();

        foreach ($actionPaths as $fullActionPath) {
            $area = 'frontend';

            $actionPath = explode('\\', $fullActionPath);
            $vendorName = array_shift($actionPath);
            $packageName = array_shift($actionPath);
            $actionClass = array_pop($actionPath);

            // Remove \Controller prefix
            array_shift($actionPath);

            if (in_array('Adminhtml', $actionPath)) {
                $area = 'adminhtml';
                array_shift($actionPath);
            }

            if (!$actionPath) {
                $actionPath = ['Index'];
            }

            $moduleName = $vendorName . '_' . $packageName;

            // Routes that might end in one of the reserved keywords have 'Action' appended. This should reverse in
            // such cases
            if (substr($actionClass, -6) === 'Action') {
                $actionClass = substr($actionClass, 0, -6);
            }

            $moduleActions[$moduleName][$area][] = strtolower(implode('/', $actionPath) . '/' . $actionClass);
        }

        $areas = $input->getOption('area') ? [$input->getOption('area')] : $this->areaList->getCodes();
        $moduleOption = $input->getOption('module');

        foreach ($areas as $area) {
            if ($defaultRouter = $this->areaList->getDefaultRouter($area)) {
                $routes = $this->configReader->read($area)[$defaultRouter]['routes'];

                foreach ($routes as $route) {
                    $routeInfo = [
                        $area,
                        $route['frontName'],
                    ];

                    foreach ($route['modules'] as $module) {
                        if ($moduleOption !== null && $moduleOption !== $module) {
                            continue;
                        }

                        $moduleRoute = $routeInfo;
                        $moduleRoute[] = $module;

                        if (isset($moduleActions[$module][$area])) {
                            foreach ($moduleActions[$module][$area] as $action) {
                                $moduleRouteAction = $moduleRoute;
                                $moduleRouteAction[] = $route['frontName'] . '/' . $action;

                                $table[] = $moduleRouteAction;
                            }
                        }
                    }
                }
            }
        }

        $this->getHelper('table')
            ->setHeaders(
                [
                    'Area',
                    'Frontname',
                    'Module',
                    'Route',
                ]
            )
            ->renderByFormat($output, $table, $input->getOption('format'))
        ;
    }
}
