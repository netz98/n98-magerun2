<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Application;

use Composer\Autoload\ClassLoader;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use N98\Util\PharWrapper;

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
     * @return ObjectManagerInterface
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
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $params['entryPoint'] = basename(__FILE__);

        $bootstrap = Bootstrap::create($magentoRootFolder, $params);
        $objectManager = $bootstrap->getObjectManager();

        return $objectManager;
    }

    public static function loadMagentoBootstrap($magentoRootFolder)
    {
        static $loaded;

        if (!$loaded) {
            PharWrapper::init();
            $oldErrorHandler = set_error_handler(function () {
                return true;
            }, E_WARNING);
            require_once $magentoRootFolder . '/app/bootstrap.php';
            set_error_handler($oldErrorHandler, E_WARNING);
            PharWrapper::ensurePharWrapperIsRegistered();
            $loaded = true;
        }
    }
}
