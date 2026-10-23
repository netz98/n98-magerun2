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
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

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

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $missing = [];
        foreach ($this->getDefinition()->getArguments() as $argument) {
            if ($argument->isRequired() && $input->getArgument($argument->getName()) === null) {
                $missing[] = $argument->getName();
            }
        }
        $missingLabel = $this->getDefinition()->hasOption('label') && $input->getOption('label') === null;
        if (!$input->isInteractive() || (!$missing && !$missingLabel)) {
            return;
        }
        if (!$this->initializeManagement($input, $output, false)) {
            throw new \RuntimeException('Cannot collect required EAV inputs without Magento.');
        }
        if ($input->getArgument('entityType') === null) {
            $input->setArgument('entityType', $this->askContext($input, $output, 'Entity type', $this->entityChoices()));
        }
        // Validate supplied context before offering any dependent choices. Never replace it.
        $entityId = $this->entityId($input->getArgument('entityType'));
        if ($this->getDefinition()->hasArgument('attributeSetId')) {
            if ($input->getArgument('attributeSetId') === null) {
                $input->setArgument('attributeSetId', $this->askContext($input, $output, 'Attribute set', $this->setChoices($entityId)));
            }
            $setup = $this->setupFactory->create();
            $set = $this->attributeSet($setup, $entityId, $input->getArgument('attributeSetId'));
            if ($this->getDefinition()->hasArgument('attributeGroupId')) {
                $setId = (int) $set['attribute_set_id'];
                if ($input->getArgument('attributeGroupId') === null) {
                    $choices = [];
                    foreach ($this->groupRows($setId) as $group) {
                        $choices[$group['attribute_group_id']] = $group['attribute_group_name'] . ' (#' . $group['attribute_group_id'] . ')';
                    }
                    $input->setArgument('attributeGroupId', $this->askContext($input, $output, 'Attribute group', $choices));
                }
                $this->attributeGroup($setup, $entityId, $setId, $input->getArgument('attributeGroupId'));
            }
        }
        foreach (['attributeCode' => 'Attribute code', 'name' => 'Group name'] as $argument => $label) {
            if ($this->getDefinition()->hasArgument($argument) && $input->getArgument($argument) === null) {
                $input->setArgument($argument, $this->askRequiredText($input, $output, $label, $argument === 'attributeCode'));
            }
        }
        if ($missingLabel) {
            $input->setOption('label', $this->askRequiredText($input, $output, 'Admin label'));
        }
    }

    protected function entityChoices(): array
    {
        $connection = $this->resource->getConnection();
        $rows = $connection->fetchAll($connection->select()
            ->from($this->resource->getTableName('eav_entity_type'), ['entity_type_code', 'entity_type_id'])
            ->order('entity_type_code ASC'));
        $choices = [];
        foreach ($rows as $row) {
            $choices[$row['entity_type_code']] = $row['entity_type_code'] . ' (#' . $row['entity_type_id'] . ')';
        }
        return $choices;
    }

    protected function setChoices(int $entityId): array
    {
        $connection = $this->resource->getConnection();
        $rows = $connection->fetchAll($connection->select()
            ->from($this->resource->getTableName('eav_attribute_set'), ['attribute_set_id', 'attribute_set_name'])
            ->where('entity_type_id = ?', $entityId)->order('attribute_set_id ASC'));
        $choices = [];
        foreach ($rows as $row) {
            $choices[$row['attribute_set_id']] = $row['attribute_set_name'] . ' (#' . $row['attribute_set_id'] . ')';
        }
        return $choices;
    }

    private function askContext(InputInterface $input, OutputInterface $output, string $label, array $choices): string
    {
        if (!$choices) {
            throw new \InvalidArgumentException('No choices available for ' . strtolower($label) . ' in this context.');
        }
        // Match the one-based menus used by the DI/observer commands. Ordinals are
        // never record IDs, and mapping by key keeps duplicate labels unambiguous.
        $keys = array_keys($choices);
        $menu = [0 => 'Cancel (no changes)'];
        foreach (array_values($choices) as $index => $choice) {
            $menu[$index + 1] = OutputFormatter::escape($choice);
        }
        $labels = [0 => 'Cancel (no changes)', ...array_values($choices)];
        $question = new ChoiceQuestion('Please select an ' . strtolower($label) . ' (number or name; Enter cancels)', $menu, 0);
        $question->setAutocompleterValues($labels);
        $question->setMaxAttempts(1);
        $question->setValidator(function ($answer) use ($labels): int {
            $answer = (string) $answer;
            if (preg_match('/^[0-9]+$/D', $answer)) {
                if (array_key_exists($answer, $labels)) {
                    return (int) $answer;
                }
            } else {
                $matches = [];
                foreach ($labels as $index => $choice) {
                    if ($answer === $choice || $answer === preg_replace('/ \(#[0-9]+\)$/D', '', $choice)) {
                        $matches[] = $index;
                    }
                }
                if (count($matches) === 1) {
                    return $matches[0];
                }
                if (count($matches) > 1) {
                    throw new \InvalidArgumentException('Ambiguous choice name; please enter a displayed choice number.');
                }
            }
            throw new \InvalidArgumentException('Please enter a displayed choice number or an unambiguous name.');
        });
        $answer = $this->io($input, $output)->getErrorStyle()->askQuestion($question);
        if ($answer === 0) {
            throw new \RuntimeException('EAV input cancelled; no changes were made.');
        }
        return (string) $keys[$answer - 1];
    }

    private function askRequiredText(InputInterface $input, OutputInterface $output, string $label, bool $attributeCode = false): string
    {
        $question = new Question($label . ' (required; Ctrl+C cancels): ');
        $question->setMaxAttempts(1);
        $question->setValidator(function ($value) use ($label, $attributeCode): string {
            $value = trim((string) $value);
            if ($attributeCode && !preg_match('/^[a-z][a-z0-9_]{0,29}$/D', $value)) {
                throw new \InvalidArgumentException('Attribute code must start with a lowercase letter and contain at most 30 lowercase letters, digits or underscores.');
            }
            if ($value === '' || strlen($value) > 255 || preg_match('/[\x00-\x1f\x7f]/', $value)) {
                throw new \InvalidArgumentException($label . ' must contain 1-255 bytes without control characters.');
            }
            return $value;
        });
        return $this->io($input, $output)->getErrorStyle()->askQuestion($question);
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
