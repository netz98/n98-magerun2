<?php

namespace N98\Magento\Command\Eav\Attribute;

use N98\Magento\Command\AbstractMagentoCommand;
use Magento\Eav\Model\Config;

abstract class AbstractAttributeCommand extends AbstractMagentoCommand
{
    /**
     * Gets an attribute model
     * 
     * @param  string $entityType
     * @param  string $attributeCode
     * @return Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttribute($entityType, $attributeCode)
    {
        return $this
            ->getObjectManager()
            ->get(Config::class)
            ->getAttribute($entityType, $attributeCode);
    }    
}
