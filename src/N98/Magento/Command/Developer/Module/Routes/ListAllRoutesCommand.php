<?php
namespace N98\Magento\Command\Developer\Module\Routes;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListAllRoutesCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this->setName('routes:api:list')
            ->setDescription('Lists all registered API routes an their corresponding modules in this Magento installation');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            // initMagento itself should output an error if it fails to initialize.
            return 1; // Return an error code
        }

        if (!$this->isMagento2()) {
            $output->writeln('<error>This command can only be run on Magento 2 installations.</error>');
            return 1;
        }

        $output->writeln('<comment>Fetching API routes for Magento 2...</comment>');
        try {
            // Ensure Object Manager is available
            $objectManager = $this->getApplication()->getObjectManager();
            if (!$objectManager) {
                $output->writeln('<error>ObjectManager is not available. Cannot fetch routes.</error>');
                return 1;
            }

            $routerList = $objectManager->get('Magento\Framework\App\RouterList');
            $routesData = [];

            /** @var \Magento\Framework\App\RouterInterface $router */
            foreach ($routerList as $router) {
                if ($router instanceof \Magento\Webapi\Controller\Router) {
                    // Accessing protected property 'routeMatcher' via reflection
                    $reflection = new \ReflectionClass($router);
                    if ($reflection->hasProperty('routeMatcher')) {
                        $routeMatcherProperty = $reflection->getProperty('routeMatcher');
                        if (!$routeMatcherProperty->isPublic()) {
                            $routeMatcherProperty->setAccessible(true);
                        }
                        $routeMatcher = $routeMatcherProperty->getValue($router);

                        if ($routeMatcher && method_exists($routeMatcher, 'getRoutes')) {
                            $webapiRoutes = $routeMatcher->getRoutes();
                            foreach ($webapiRoutes as $httpMethod => $methodRoutes) {
                                foreach ($methodRoutes as $routePath => $routeConfig) {
                                    $serviceClass = $routeConfig['service']['class'] ?? 'N/A';
                                    $serviceMethod = $routeConfig['service']['method'] ?? 'N/A';
                                    $routesData[] = [
                                        'area' => 'webapi',
                                        'route_path' => $routePath,
                                        'method' => strtoupper($httpMethod),
                                        'handler' => $serviceClass . '::' . $serviceMethod,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($routesData)) {
                $table = new Table($output);
                $table->setHeaders(['Area', 'Route Path', 'HTTP Method', 'Handler/Service']);
                foreach ($routesData as $route) {
                    $table->addRow([$route['area'], $route['route_path'], $route['method'], $route['handler']]);
                }
                $table->render();
            } else {
                $output->writeln('<info>No specific API routes found via Webapi Router.</info>');
                // Optionally, could list all modules as a fallback like before,
                // but the request was to simplify and focus on M2 API routes.
                // For now, just indicating no specific API routes were found is cleaner.
            }

        } catch (\Throwable $e) { // Catching Throwable for broader compatibility (PHP 7+)
            $output->writeln('<error>Error fetching Magento 2 API routes: ' . $e->getMessage() . '</error>');
            if ($output->isVerbose()) {
                $output->writeln((string)$e);
            }
            return 1;
        }

        return 0;
    }
}
