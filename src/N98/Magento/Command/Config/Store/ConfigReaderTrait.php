<?php

namespace N98\Magento\Command\Config\Store;

use Magento\Framework\App\Config\ScopePool;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

trait ConfigReaderTrait
{
    /**
     * @var \Magento\Framework\App\Config\ScopePoolInterface
     */
    protected $_scopePool;

    /**
     * @return \Magento\Framework\Encryption\EncryptorInterface
     */
    protected function getEncryptionModel()
    {
        return $this->getObjectManager()->get('\Magento\Framework\Encryption\EncryptorInterface');
    }

    /**
     * @return \Magento\Framework\App\Config
     */
    protected function _getConfigModel()
    {
        return $this->getObjectManager()->get('\Magento\Framework\App\Config');
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function _getStoreManager()
    {
        return $this->getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * @return \Magento\Framework\App\Config\ScopePoolInterface
     */
    protected function getScopePool()
    {
        if (!$this->_scopePool) {
            $this->_scopePool = $this->getObjectManager()->get(ScopePool::class);
        }

        return $this->_scopePool;
    }

    /**
     * @param string $scope
     * @return \Magento\Framework\App\Config\Data
     */
    protected function getScope($scope)
    {
        return $this->getScopePool()->getScope($scope);
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
        $scopeConfig = $this->getObjectManager()->get(ScopeConfigInterface::class);

        return $scopeConfig->getValue($path, $scope, $scopeId);
    }
}
