<?php

namespace N98\Magento\Application;

use Composer\Autoload\ClassLoader;
use Magento\Framework\Autoload\AutoloaderRegistry;
use N98\Magento\Framework\App\Magerun;

/**
 * Class Magento2Initializer
 * @package N98\Magento\Application
 */
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
        self::loadMagentoBootstrap($magentoRootFolder);

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

    public static function loadMagentoBootstrap($magentoRootFolder)
    {
        \N98\Util\PharWrapper::init();
        $oldErrorHandler = set_error_handler(function() { return true; }, E_WARNING);
        require_once $magentoRootFolder . '/app/bootstrap.php';
        set_error_handler($oldErrorHandler, E_WARNING);
        \N98\Util\PharWrapper::ensurePharWrapperIsRegistered();
    }
}
