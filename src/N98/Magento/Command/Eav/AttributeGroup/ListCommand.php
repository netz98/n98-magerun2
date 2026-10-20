<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\AttributeGroup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractGroupCommand
{
    protected function configure()
    {
        $this->setName('eav:attribute-group:list')->setDescription('List EAV attribute groups in an attribute set');
        $this->configureContext();
        $this->addFormatOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->initializeManagement($input, $output, false)) {
            return Command::FAILURE;
        }
        [, , $setId] = $this->context($input);
        $rows = [];
        foreach ($this->groupRows($setId) as $group) {
            $rows[] = [$group['attribute_group_id'], $group['attribute_group_name'], $setId, $group['sort_order'], $group['default_id']];
        }
        $this->getHelper('table')->setHeaders(['id', 'name', 'attribute_set_id', 'sort_order', 'default_id'])
            ->renderByFormat($output, $rows, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
