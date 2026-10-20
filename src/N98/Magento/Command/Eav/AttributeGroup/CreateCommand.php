<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\AttributeGroup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends AbstractGroupCommand
{
    protected function configure()
    {
        $this->setName('eav:attribute-group:create')->setDescription('Create an EAV attribute group');
        $this->configureContext();
        $this->addArgument('name', InputArgument::REQUIRED, 'New group name')
            ->addOption('sort-order', null, InputOption::VALUE_REQUIRED, 'Non-negative sort order', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->initializeManagement($input, $output, true)) {
            return Command::FAILURE;
        }
        [$setup, $entityId, $setId] = $this->context($input);
        $name = trim($input->getArgument('name'));
        $sortOrder = $input->getOption('sort-order');
        if ($name === '' || strlen($name) > 255 || preg_match('/[\x00-\x1f\x7f]/', $name)) {
            throw new \InvalidArgumentException('Group name must contain 1-255 bytes without control characters.');
        }
        if (!preg_match('/^[0-9]+$/D', $sortOrder) || (float) $sortOrder > 2147483647) {
            throw new \InvalidArgumentException('Sort order must be a non-negative 32-bit integer.');
        }
        // Magento derives a group code from the name; reject code collisions too.
        $code = $setup->convertToAttributeGroupCode($name);
        if ($code === '' || is_numeric($code)) {
            throw new \InvalidArgumentException('Group name must produce a non-empty, non-numeric attribute group code.');
        }
        foreach ($this->groupRows($setId) as $group) {
            if (
                strcasecmp($group['attribute_group_name'], $name) === 0
                || strcasecmp((string) $group['attribute_group_code'], $code) === 0
                || ($code === 'general' && (int) $group['default_id'] === 1)
            ) {
                throw new \InvalidArgumentException('Attribute group already exists in this attribute set.');
            }
        }
        $setup->addAttributeGroup($entityId, $setId, $name, (int) $sortOrder);
        $id = $setup->getAttributeGroupByCode($entityId, $setId, $code, 'attribute_group_id');
        $this->clearEavCache($setup);
        $output->writeln(sprintf('<info>Created attribute group ID: %d in attribute set %d</info>', $id, $setId));

        return Command::SUCCESS;
    }
}
