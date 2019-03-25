<?php

namespace N98\Magento\Application;

use Composer\Autoload\ClassLoader;
use Magento\Framework\Autoload\AutoloaderRegistry;
use N98\Magento\Framework\App\Magerun;

class Magento2Initializer
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    private $autoloader;

    /**
     * Magento2Initializer constructor.
     * @param \Composer\Autoload\ClassLoader $autoloader
     */
    public function __construct(ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * @param string $magentoRootFolder
     * @return \N98\Magento\Framework\App\Magerun
     * @throws \Exception
     */
    public function init($magentoRootFolder)
    {
        $this->requireOnce($magentoRootFolder . '/app/bootstrap.php');
        \stream_wrapper_restore('phar');

        $magentoAutoloader = AutoloaderRegistry::getAutoloader();

        // Prevent an infinite loop of autoloaders
        if (!$magentoAutoloader instanceof AutoloaderDecorator) {
            AutoloaderRegistry::registerAutoloader(
                new AutoloaderDecorator(
                    $magentoAutoloader,
                    $this->autoloader
                )
            );
        }

        $params = $_SERVER;
        $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[\Magento\Store\Model\Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $params['entryPoint'] = basename(__FILE__);

        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
        /** @var \Magento\Framework\App\Cron $app */
        $app = $bootstrap->createApplication(Magerun::class, []);
        /* @var $app \N98\Magento\Framework\App\Magerun */
        $app->launch();

        return $app;
    }

    /**
     * use require-once inside a function with it's own variable scope w/o any other variables
     * and $this unbound.
     *
     * @param string $path
     */
    private function requireOnce($path)
    {
        $requireOnce = function () {
            require_once func_get_arg(0);
        };
        if (50400 <= PHP_VERSION_ID) {
            $requireOnce->bindTo(null);
        }

        $requireOnce($path);
    }
}
