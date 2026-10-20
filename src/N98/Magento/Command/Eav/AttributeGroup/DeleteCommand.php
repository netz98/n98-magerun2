<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\AttributeGroup;

use Magento\Eav\Setup\EavSetup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeleteCommand extends AbstractGroupCommand
{
    protected function configure()
    {
        $this->setName('eav:attribute-group:delete')->setDescription('Delete an empty, non-default EAV attribute group');
        $this->configureContext();
        $this->addArgument('attributeGroupId', InputArgument::REQUIRED, 'Attribute group ID in the specified set')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation (not safety checks)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->initializeManagement($input, $output, true)) {
            return Command::FAILURE;
        }
        [$setup, $entityId, $setId] = $this->context($input);
        $groupId = $this->positiveId($input->getArgument('attributeGroupId'));
        $this->assertDeletable($setup, $entityId, $setId, $groupId);
        if (!$input->getOption('force')) {
            $confirmed = $input->isInteractive() && $this->getHelper('question')->ask(
                $input,
                $output,
                new ConfirmationQuestion('Delete this empty attribute group? [y/N] ', false)
            );
            if (!$confirmed) {
                $output->writeln('<comment>Deletion cancelled. Use --force for non-interactive deletion.</comment>');
                return Command::FAILURE;
            }
        }
        $this->assertDeletable($setup, $entityId, $setId, $groupId);
        $setup->removeAttributeGroup($entityId, $setId, $groupId);
        $this->clearEavCache($setup);
        $output->writeln(sprintf('<info>Deleted attribute group ID: %d from attribute set %d</info>', $groupId, $setId));

        return Command::SUCCESS;
    }

    private function assertDeletable(EavSetup $setup, int $entityId, int $setId, int $groupId): void
    {
        $group = $this->attributeGroup($setup, $entityId, $setId, $groupId);
        if (!empty($group['default_id']) || (int) $setup->getDefaultAttributeGroupId($entityId, $setId) === $groupId) {
            throw new \InvalidArgumentException('Cannot delete a default attribute group, even with --force.');
        }
        if ($this->assignedAttributes($setId, $groupId)) {
            throw new \InvalidArgumentException('Cannot delete a populated attribute group, even with --force.');
        }
    }
}
