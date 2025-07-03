<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Route;

use Magento\Framework\App\Action\HttpConnectActionInterface;
use Magento\Framework\App\Action\HttpDeleteActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpOptionsActionInterface;
use Magento\Framework\App\Action\HttpPatchActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpPropfindActionInterface;
use Magento\Framework\App\Action\HttpPutActionInterface;
use Magento\Framework\App\Action\HttpTraceActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Route\Config;
use Magento\Framework\Module\Dir\Reader;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var Config\Reader
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
        Reader        $reader,
        Config        $config,
        AreaList      $areaList,
        Config\Reader $configReader
    ) {
        $this->reader = $reader;
        $this->config = $config;
        $this->areaList = $areaList;
        $this->configReader = $configReader;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = [];
        $moduleActions = [];

        $actionPaths = $this->reader->getActionFiles();

        foreach ($actionPaths as $fullActionPath) {
            /**
             * Filter abstract classes and non action classes
             * @link https://github.com/netz98/n98-magerun2/issues/1304
             */
            if (!is_a($fullActionPath, ActionInterface::class, true)) {
                continue;
            }

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

            $moduleActions[$moduleName][$area][] = [
                'file' => strtolower(implode('/', $actionPath) . '/' . $actionClass),
                'class' => $fullActionPath,
            ];
        }

        $areas = $input->getOption('area') ? [$input->getOption('area')] : $this->areaList->getCodes();
        $moduleOption = $input->getOption('module');

        foreach ($areas as $area) {
            $table = $this->processArea($area, $moduleOption, $moduleActions, $table);
        }

        $this->getHelper('table')
            ->setHeaders(
                [
                    'Area',
                    'Frontname',
                    'Module',
                    'Route',
                    'Methods'
                ]
            )
            ->renderByFormat($output, $table, $input->getOption('format'))
        ;

        return Command::SUCCESS;
    }

    /**
     * @param $area
     * @param $moduleOption
     * @param array $moduleActions
     * @param array $table
     * @return array
     */
    private function processArea($area, $moduleOption, array $moduleActions, array $table): array
    {
        if ($defaultRouter = $this->areaList->getDefaultRouter($area)) {
            $routes = $this->configReader->read($area)[$defaultRouter]['routes'];

            foreach ($routes as $route) {
                $table = $this->processSingleRoute($area, $route, $moduleOption, $moduleActions, $table);
            }
        }
        return $table;
    }

    /**
     * @param $area
     * @param $route
     * @param $moduleOption
     * @param array $moduleActions
     * @param array $table
     * @return array
     */
    protected function processSingleRoute($area, $route, $moduleOption, array $moduleActions, array $table): array
    {
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
                    $moduleRouteAction[] = $route['frontName'] . '/' . ActionPathFormatter::format($action['file']);
                    $moduleRouteAction[] = implode(',', $this->getSupportedHttpVerbs($action['class']));

                    $table[] = $moduleRouteAction;
                }
            }
        }
        return $table;
    }

    /**
     * Returns the HTTP verb of route by class of action
     *
     * @param string $actionClass
     * @return string[]
     */
    private function getSupportedHttpVerbs(string $actionClass): array
    {
        $verbs = [];

        if (is_a($actionClass, HttpConnectActionInterface::class, true)) {
            $verbs[] = 'CONNECT';
        }

        if (is_a($actionClass, HttpDeleteActionInterface::class, true)) {
            $verbs[] = 'DELETE';
        }

        if (is_a($actionClass, HttpGetActionInterface::class, true)) {
            $verbs[] = 'GET';
        }

        if (is_a($actionClass, HttpOptionsActionInterface::class, true)) {
            $verbs[] = 'OPTIONS';
        }

        if (is_a($actionClass, HttpPatchActionInterface::class, true)) {
            $verbs[] = 'PATCH';
        }

        if (is_a($actionClass, HttpPostActionInterface::class, true)) {
            $verbs[] = 'POST';
        }

        if (is_a($actionClass, HttpPropfindActionInterface::class, true)) {
            $verbs[] = 'PROPFIND';
        }

        if (is_a($actionClass, HttpPutActionInterface::class, true)) {
            $verbs[] = 'PUT';
        }

        if (is_a($actionClass, HttpTraceActionInterface::class, true)) {
            $verbs[] = 'TRACE';
        }

        if (count($verbs) === 0) {
            return ['GET', 'POST'];
        }

        return $verbs;
    }
}
