<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\Attribute;

use N98\Magento\Command\Eav\AbstractManagementCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends AbstractManagementCommand
{
    protected function configure()
    {
        $this->setName('eav:attribute:create')->setDescription('Create a user-defined EAV attribute')
            ->addArgument('entityType', InputArgument::REQUIRED, 'Entity type code, e.g. catalog_product')
            ->addArgument('attributeCode', InputArgument::REQUIRED, 'New attribute code')
            ->addOption('label', null, InputOption::VALUE_REQUIRED, 'Admin label (required)')
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'text, textarea, date, boolean, select or multiselect', 'text')
            ->addOption('required', null, InputOption::VALUE_REQUIRED, 'Require a value: 0 or 1', '0')
            ->addOption('unique', null, InputOption::VALUE_REQUIRED, 'Require unique values: 0 or 1', '0')
            ->addOption('visible', null, InputOption::VALUE_REQUIRED, 'Admin visibility: 0 or 1', '1')
            ->addOption('scope', null, InputOption::VALUE_REQUIRED, 'Catalog scope: global, website or store', 'global')
            ->addOption('default', null, InputOption::VALUE_REQUIRED, 'Default for text, textarea or boolean')
            ->addOption('option', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Admin option label for select/multiselect (repeatable)')
            ->addOption('sort-order', null, InputOption::VALUE_REQUIRED, 'Sort order, 0-32767', '0')
            ->addOption('attribute-set', null, InputOption::VALUE_REQUIRED, 'Assign to this attribute set ID (requires --attribute-group)')
            ->addOption('attribute-group', null, InputOption::VALUE_REQUIRED, 'Assign to this group ID within --attribute-set');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->initializeManagement($input, $output, true)) {
            return Command::FAILURE;
        }
        if ($input->getOption('label') === null) {
            throw new \InvalidArgumentException('Missing required option --label. Supply it explicitly when using --no-interaction.');
        }
        $code = $input->getArgument('attributeCode');
        $data = (new CreationMetadata())->build($code, $input->getOptions());
        $entityCode = $input->getArgument('entityType');
        $entityId = $this->entityId($entityCode);
        if ($entityCode !== 'catalog_product' && $entityCode !== 'catalog_category' && $input->getOption('scope') !== 'global') {
            throw new \InvalidArgumentException('Non-global scope is only supported for catalog entities.');
        }
        $setup = $this->setupFactory->create();
        if ($this->eavConfig->getAttribute($entityId, $code)->getId()) {
            throw new \InvalidArgumentException('Attribute already exists for this entity type.');
        }
        $setId = $input->getOption('attribute-set');
        $groupId = $input->getOption('attribute-group');
        if (($setId === null) !== ($groupId === null)) {
            throw new \InvalidArgumentException('--attribute-set and --attribute-group must be supplied together.');
        }
        if ($setId !== null) {
            $set = $this->attributeSet($setup, $entityId, $setId);
            $group = $this->attributeGroup($setup, $entityId, (int) $set['attribute_set_id'], $groupId);
            $setId = (int) $set['attribute_set_id'];
            $groupId = (int) $group['attribute_group_id'];
        }
        // Attribute metadata, options and optional assignment form one operation.
        $connection = $this->resource->getConnection();
        $connection->beginTransaction();
        try {
            $setup->addAttribute($entityId, $code, $data);
            if ($setId !== null) {
                $setup->addAttributeToSet($entityId, $setId, $groupId, $code, $data['sort_order']);
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
            $this->eavConfig->clear();
            throw $exception;
        }
        $this->clearEavCache($setup);
        $output->writeln(sprintf('<info>Created attribute "%s" for entity type "%s"</info>', $code, $entityCode));

        return Command::SUCCESS;
    }
}
