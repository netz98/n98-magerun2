<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\Attribute;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use N98\Magento\Command\AbstractMagentoCommand;

/**
 * Class AbstractAttributeCommand
 * @package N98\Magento\Command\Eav\Attribute
 */
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
