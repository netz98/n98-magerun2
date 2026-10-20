<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractManagementCommand extends AbstractMagentoCommand
{
    protected Config $eavConfig;
    protected EavSetupFactory $setupFactory;
    protected ResourceConnection $resource;

    public function inject(Config $eavConfig, EavSetupFactory $setupFactory, ResourceConnection $resource)
    {
        $this->eavConfig = $eavConfig;
        $this->setupFactory = $setupFactory;
        $this->resource = $resource;
    }

    protected function initializeManagement(InputInterface $input, OutputInterface $output, bool $mutation): bool
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return false;
        }
        if ($mutation && $this->runsInProductionMode($input, $output)) {
            $output->writeln('<error>This command is not available in production mode</error>');
            return false;
        }

        return true;
    }

    protected function entityId(string $code): int
    {
        $entity = $this->eavConfig->getEntityType($code);
        if (!$entity->getId() || $entity->getEntityTypeCode() !== $code) {
            throw new \InvalidArgumentException('Unknown entity type code: ' . $code);
        }

        return (int) $entity->getId();
    }

    protected function positiveId($value): int
    {
        if (!preg_match('/^[1-9][0-9]*$/D', (string) $value) || (float) $value > PHP_INT_MAX) {
            throw new \InvalidArgumentException('Expected a positive numeric ID.');
        }

        return (int) $value;
    }

    protected function attributeSet(EavSetup $setup, int $entityId, $setId): array
    {
        $set = $setup->getAttributeSet($entityId, $this->positiveId($setId));
        if (!$set || (int) $set['entity_type_id'] !== $entityId) {
            throw new \InvalidArgumentException('Attribute set was not found for this entity type.');
        }

        return $set;
    }

    protected function attributeGroup(EavSetup $setup, int $entityId, int $setId, $groupId): array
    {
        $group = $setup->getAttributeGroup($entityId, $setId, $this->positiveId($groupId));
        if (!$group || (int) $group['attribute_set_id'] !== $setId) {
            throw new \InvalidArgumentException('Attribute group was not found in this attribute set.');
        }

        return $group;
    }

    protected function groupRows(int $setId): array
    {
        $connection = $this->resource->getConnection();

        return $connection->fetchAll($connection->select()
            ->from($this->resource->getTableName('eav_attribute_group'))
            ->where('attribute_set_id = ?', $setId)
            ->order(['sort_order ASC', 'attribute_group_id ASC']));
    }

    protected function assignedAttributes(int $setId, int $groupId): array
    {
        $connection = $this->resource->getConnection();

        return $connection->fetchAll($connection->select()
            ->from(['ea' => $this->resource->getTableName('eav_entity_attribute')], ['attribute_id', 'sort_order'])
            ->join(['a' => $this->resource->getTableName('eav_attribute')], 'a.attribute_id = ea.attribute_id', ['attribute_code', 'frontend_label'])
            ->where('ea.attribute_set_id = ?', $setId)
            ->where('ea.attribute_group_id = ?', $groupId)
            ->order(['ea.sort_order ASC', 'a.attribute_code ASC']));
    }

    protected function clearEavCache(EavSetup $setup): void
    {
        $setup->cleanCache();
        $this->eavConfig->clear();
    }
}
