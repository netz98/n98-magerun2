<?php

namespace N98\Magento\Command\Config;

use Magento\Framework\ObjectManager\ObjectManager;
use N98\Magento\Command\AbstractMagentoCommand;

abstract class AbstractConfigCommand extends AbstractMagentoCommand
{
    const DISPLAY_NULL_UNKOWN_VALUE = 'NULL (NULL/"unkown" value)';

    /**
     * \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopePoolInterface
     */
    protected $_scopePool;

    /**
     * @var array
     */
    protected $_scopes = array();

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
        return $this->getObjectManager()->get('\Magento\Store\Model\StoreManagerInterface');
    }

    /**
     * @return \Magento\Framework\App\Config\ScopePoolInterface
     */
    protected function getScopePool()
    {
        if (!$this->_scopePool) {
            $this->_scopePool = $this->getObjectManager()->get('\Magento\Framework\App\Config\ScopePool');
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
     * @return \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected function getConfigWriter()
    {
        if (!$this->configWriter) {
            /** @var ObjectManager $objectManager */
            $objectManager = $this->getObjectManager();
            $this->configWriter = $objectManager->get('\Magento\Framework\App\Config\Storage\WriterInterface');
        }

        return $this->configWriter;
    }

    /**
     * @param string $value
     * @param string $encryptionType
     * @return string
     */
    protected function _formatValue($value, $encryptionType)
    {
        if ($value === null) {
            $formatted = $value;
        } elseif ($encryptionType === 'encrypt') {
            $formatted = $this->getEncryptionModel()->encrypt($value);
        } elseif ($encryptionType === 'decrypt') {
            $formatted = $this->getEncryptionModel()->decrypt($value);
        } else {
            $formatted = $value;
        }

        return $formatted;
    }

    /**
     * @param string $scope
     */
    protected function _validateScopeParam($scope)
    {
        if (!in_array($scope, $this->_scopes)) {
            throw new \InvalidArgumentException(
                'Invalid scope parameter. It must be one of ' . implode(',', $this->_scopes)
            );
        }
    }

    /**
     * @param string $scope
     * @param string $scopeId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _convertScopeIdParam($scope, $scopeId)
    {
        if (null === $scopeId && in_array($scope, array('websites', 'stores'), true)) {
            return $scopeId;
        }

        if ($scope === 'websites' && !is_numeric($scopeId)) {
            $website = $this->_getStoreManager()->getWebsite($scopeId);

            if (!$website) {
                throw new \InvalidArgumentException('Invalid scope parameter. Website does not exist.');
            }

            return $website->getId();
        }

        if ($scope === 'stores' && !is_numeric($scopeId)) {
            $store = $this->_getStoreManager()->getStore($scopeId);

            if (!$store) {
                throw new \InvalidArgumentException('Invalid scope parameter. Store does not exist.');
            }

            return $store->getId();
        }

        return $scopeId;
    }
}
