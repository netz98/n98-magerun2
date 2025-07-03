<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Store;

use Magento\Framework\App\Config\Storage\WriterInterface;

trait ConfigWriterTrait
{
    /**
     * \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @return \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected function getConfigWriter()
    {
        if (!$this->configWriter) {
            /** @var ObjectManager $objectManager */
            $objectManager = $this->getObjectManager();
            $this->configWriter = $objectManager->get(WriterInterface::class);
        }

        return $this->configWriter;
    }

    /**
     * @param $path
     * @param $value
     * @param $scope
     * @param $scopeId
     * @return void
     */
    protected function saveScopeConfigValue($path, $value, $scope, $scopeId)
    {
        return $this->getConfigWriter()->save(
            $path,
            $value,
            $scope,
            $scopeId
        );
    }
}
