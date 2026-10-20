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
use Symfony\Component\Console\Output\OutputInterface;

class ViewCommand extends AbstractGroupCommand
{
    protected function configure()
    {
        $this->setName('eav:attribute-group:view')->setDescription('View an EAV attribute group and its assigned attributes');
        $this->configureContext();
        $this->addArgument('attributeGroupId', InputArgument::REQUIRED, 'Attribute group ID in the specified set')
            ->addFormatOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->initializeManagement($input, $output, false)) {
            return Command::FAILURE;
        }
        [$setup, $entityId, $setId] = $this->context($input);
        $group = $this->attributeGroup($setup, $entityId, $setId, $input->getArgument('attributeGroupId'));
        $rows = [];
        foreach ($group as $key => $value) {
            $rows[] = [$key, $value];
        }
        foreach ($this->assignedAttributes($setId, (int) $group['attribute_group_id']) as $attribute) {
            $rows[] = ['attribute.' . $attribute['attribute_code'], sprintf(
                'ID: %d; label: %s; sort order: %d',
                $attribute['attribute_id'],
                $attribute['frontend_label'],
                $attribute['sort_order']
            )];
        }
        $this->getHelper('table')->setHeaders(['property', 'value'])
            ->renderByFormat($output, $rows, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
