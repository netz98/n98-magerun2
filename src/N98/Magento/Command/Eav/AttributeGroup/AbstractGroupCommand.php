<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\AttributeGroup;

use N98\Magento\Command\Eav\AbstractManagementCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractGroupCommand extends AbstractManagementCommand
{
    protected function configureContext(): void
    {
        $this->addArgument('entityType', InputArgument::REQUIRED, 'Entity type code, e.g. catalog_product')
            ->addArgument('attributeSetId', InputArgument::REQUIRED, 'Attribute set ID belonging to the entity type');
    }

    protected function context(InputInterface $input): array
    {
        $entityId = $this->entityId($input->getArgument('entityType'));
        $setup = $this->setupFactory->create();
        $set = $this->attributeSet($setup, $entityId, $input->getArgument('attributeSetId'));

        return [$setup, $entityId, (int) $set['attribute_set_id']];
    }
}
