<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Store;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

trait ConfigReaderTrait
{
    /**
     * @return \Magento\Framework\Encryption\EncryptorInterface
     */
    protected function getEncryptionModel()
    {
        return $this->getObjectManager()->get(\Magento\Framework\Encryption\EncryptorInterface::class);
    }

    /**
     * @return \Magento\Framework\App\Config
     */
    protected function _getConfigModel()
    {
        return $this->getObjectManager()->get(\Magento\Framework\App\Config::class);
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function _getStoreManager()
    {
        return $this->getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Returns a Magento scope config value (also known as store config)
     *
     * @param string $path
     * @param string $scope
     * @param string|int $scopeId
     * @return string
     */
    protected function getScopeConfigValue($path, $scope, $scopeId = null)
    {
        return $this->getObjectManager()->get(ScopeConfigInterface::class)->getValue($path, $scope, $scopeId);
    }
}
