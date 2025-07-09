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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Filesystem\DirectoryList as FsDirectoryList;
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
        $params = $this->resolveDocumentRoot($magentoRootFolder, $params);

        $bootstrap = Bootstrap::create($magentoRootFolder, $params);
        $objectManager = $bootstrap->getObjectManager();

        return $objectManager;
    }

    private function resolveDocumentRoot(string $magentoRootFolder, array $params): array
    {
        $envFile = $magentoRootFolder . '/app/etc/env.php';
        if (is_readable($envFile)) {
            $env = include $envFile;
            if (!empty($env['directories']['document_root_is_pub'])) {
                $params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] = [
                    DirectoryList::PUB => [FsDirectoryList::URL_PATH => ''],
                    DirectoryList::MEDIA => [FsDirectoryList::URL_PATH => 'media'],
                    DirectoryList::STATIC_VIEW => [FsDirectoryList::URL_PATH => 'static'],
                    DirectoryList::UPLOAD => [FsDirectoryList::URL_PATH => 'media/upload'],
                ];
            }
        }

        return $params;
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
