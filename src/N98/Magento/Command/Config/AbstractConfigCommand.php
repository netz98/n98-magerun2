<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Config\Store\ConfigReaderTrait;
use N98\Magento\Command\Config\Store\ConfigWriterTrait;

abstract class AbstractConfigCommand extends AbstractMagentoCommand
{
    use ConfigWriterTrait;
    use ConfigReaderTrait;

    const DISPLAY_NULL_UNKNOWN_VALUE = 'NULL (NULL/"unknown" value)';

    /**
     * @var array
     */
    protected $_scopes = [];

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
        if (null === $scopeId && in_array($scope, ['websites', 'stores'], true)) {
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
