<?php

namespace N98\Magento\Command\Config;

use N98\Magento\Command\AbstractMagentoCommand;

abstract class AbstractConfigCommand extends AbstractMagentoCommand
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
        return $this->getObjectManager('\Magento\Framework\App\Config');
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
        return $this->getObjectManager()->get('\Magento\Framework\App\Config\Storage\WriterInterface');
    }

    /**
     * @param string $value
     * @param string $encryptionType
     * @return string
     */
    protected function _formatValue($value, $encryptionType)
    {
        if ($encryptionType == 'encrypt') {
            $value = $this->getEncryptionModel()->encrypt($value);
        } elseif ($encryptionType == 'decrypt') {
            $value = $this->getEncryptionModel()->decrypt($value);
        }

        return $value;
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
     */
    protected function _convertScopeIdParam($scope, $scopeId)
    {
        if ($scope == 'websites' && !is_numeric($scopeId)) {
            $website = \Mage::app()->getWebsite($scopeId);
            if (!$website) {
                throw new \InvalidArgumentException('Invalid scope parameter. Website does not exist.');
            }

            return $website->getId();
        }

        if ($scope == 'stores' && !is_numeric($scopeId)) {
            $store = \Mage::app()->getStore($scopeId);
            if (!$store) {
                throw new \InvalidArgumentException('Invalid scope parameter. Store does not exist.');
            }

            return $store->getId();
        }

        return $scopeId;
    }
}
