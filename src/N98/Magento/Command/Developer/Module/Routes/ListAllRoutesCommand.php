<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Routes;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListAllRoutesCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this->setName('routes:api:list')
            ->setDescription('Lists all registered API routes and their corresponding modules in this Magento installation')
            ->addOption('area', 'a', InputOption::VALUE_REQUIRED, 'Filter routes by area (e.g. webapi)')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Filter routes by path pattern (partial match)')
            ->addOption('method', 'm', InputOption::VALUE_REQUIRED, 'Filter routes by HTTP method (e.g. GET, POST)')
            ->addFormatOption();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return 1;
        }

        $format = $input->getOption('format');
        $areaFilter = $input->getOption('area');
        $pathFilter = $input->getOption('path');
        $methodFilter = $input->getOption('method');

        $output->writeln('<comment>Fetching API routes for Magento 2...</comment>');
        try {
            $objectManager = $this->getApplication()->getObjectManager();
            if (!$objectManager) {
                $output->writeln('<error>ObjectManager is not available. Cannot fetch routes.</error>');
                return 1;
            }

            if (class_exists('Magento\Webapi\Model\Config')) {
                $webapiConfig = $objectManager->get('Magento\Webapi\Model\Config');
            } elseif (interface_exists('Magento\Webapi\Model\ConfigInterface')) {
                $webapiConfig = $objectManager->get('Magento\Webapi\Model\ConfigInterface');
            } else {
                $output->writeln('<error>Webapi module is not available in this Magento installation.</error>');
                return 1;
            }

            $services = $webapiConfig->getServices();
            $routesData = [];

            if (!empty($services['routes'])) {
                foreach ($services['routes'] as $routePath => $methods) {
                    if ($pathFilter !== null && stripos($routePath, $pathFilter) === false) {
                        continue;
                    }

                    foreach ($methods as $httpMethod => $config) {
                        if ($methodFilter !== null && strcasecmp($httpMethod, $methodFilter) !== 0) {
                            continue;
                        }

                        $area = 'webapi';
                        if ($areaFilter !== null && strcasecmp($area, $areaFilter) !== 0) {
                            continue;
                        }

                        $serviceClass = $config['service']['class'] ?? 'N/A';
                        $serviceMethod = $config['service']['method'] ?? 'N/A';
                        $routesData[] = [
                            'area' => $area,
                            'route_path' => $routePath,
                            'method' => strtoupper($httpMethod),
                            'handler' => $serviceClass . '::' . $serviceMethod,
                        ];
                    }
                }
            }

            if (!empty($routesData)) {
                // Sort routes for better readability
                usort($routesData, function ($a, $b) {
                    $pathCompare = strcmp($a['route_path'], $b['route_path']);
                    if ($pathCompare !== 0) {
                        return $pathCompare;
                    }
                    return strcmp($a['method'], $b['method']);
                });

                $table = [];
                foreach ($routesData as $route) {
                    $methodStr = $route['method'];
                    $pathStr = $route['route_path'];
                    if ($format === null && $output->isDecorated()) {
                        $methodStr = match ($methodStr) {
                            'GET' => '<fg=green;options=bold>GET</>',
                            'POST' => '<fg=cyan;options=bold>POST</>',
                            'PUT' => '<fg=yellow;options=bold>PUT</>',
                            'PATCH' => '<fg=yellow;options=bold>PATCH</>',
                            'DELETE' => '<fg=red;options=bold>DELETE</>',
                            default => $methodStr
                        };
                        $pathStr = preg_replace('/:([a-zA-Z0-9_]+)/', '<fg=magenta>:$1</>', $pathStr);
                    }
                    $table[] = [
                        $route['area'],
                        $pathStr,
                        $methodStr,
                        $route['handler'],
                    ];
                }

                $this->getHelper('table')
                    ->setTitle('API Routes')
                    ->setHeaders(['area', 'route_path', 'method', 'handler'])
                    ->renderByFormat($output, $table, $format);
            } else {
                $output->writeln('<info>No specific API routes found matching the filters.</info>');
            }

        } catch (\Throwable $e) {
            $output->writeln('<error>Error fetching Magento 2 API routes: ' . $e->getMessage() . '</error>');
            if ($output->isVerbose()) {
                $output->writeln((string)$e);
            }
            return 1;
        }

        return 0;
    }
}
