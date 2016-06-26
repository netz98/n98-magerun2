<?php

namespace N98\Magento\Command\Eav\Attribute;

use N98\Magento\Command\AbstractMagentoCommand;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

abstract class AbstractAttributeCommand extends AbstractMagentoCommand
{
    /**
     * Gets an attribute model
     *
     * @param string $entityType
     * @param string $attributeCode
     * @return AbstractAttribute
     */
    public function getAttribute($entityType, $attributeCode)
    {
        return $this
            ->getObjectManager()
            ->get(Config::class)
            ->getAttribute($entityType, $attributeCode);
    }
}
