<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\StoreGroup;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use Magento\Framework\Registry;
use Magento\Store\Model\GroupFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends AbstractMagentoCommand
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var Registry
     */
    private $registry;

    protected function configure()
    {
        $this
            ->setName('sys:store-group:delete')
            ->addArgument('id', InputArgument::OPTIONAL, 'Store group ID')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Delete without confirmation')
            ->setDescription('Delete an existing store group');
    }

    public function inject(GroupFactory $groupFactory, Registry $registry)
    {
        $this->groupFactory = $groupFactory;
        $this->registry = $registry;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('id');
        if ($identifier === null) {
            $identifier = $this->selectStoreGroup();
        }

        if ($identifier === null) {
            $output->writeln('<info>No store groups found.</info>');

            return Command::SUCCESS;
        }

        if (!ctype_digit((string) $identifier) || (int) $identifier < 1) {
            throw new RuntimeException('The store group ID must be a positive integer.');
        }

        $group = $this->groupFactory->create()->load((int) $identifier);
        if (!$group->getId()) {
            throw new RuntimeException(sprintf('Store group with ID "%s" does not exist.', $identifier));
        }

        if ((int) $group->getWebsite()->getDefaultGroupId() === (int) $group->getId()) {
            throw new RuntimeException('The default store group cannot be deleted.');
        }

        if (!$group->isCanDelete()) {
            throw new RuntimeException('The store group cannot be deleted because it is the only group of its website.');
        }

        if (!$input->getOption('force') && !confirm(
            sprintf(
                '<question>Are you sure you want to delete store group "%s" (ID: %d)?</question>',
                $group->getName(),
                $group->getId()
            ),
            default: false
        )) {
            $output->writeln('<error>Operation cancelled.</error>');

            return Command::FAILURE;
        }

        $isSecure = $this->registry->registry('isSecureArea');
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        try {
            $group->delete();
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        } finally {
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', $isSecure);
        }

        $output->writeln(
            sprintf(
                '<info>Successfully deleted store group <comment>%s</comment> with ID: <comment>%d</comment></info>',
                $group->getName(),
                $group->getId()
            )
        );

        return Command::SUCCESS;
    }

    private function selectStoreGroup(): ?string
    {
        $options = [];
        foreach ($this->groupFactory->create()->getCollection()->getItems() as $group) {
            $options[(string) $group->getId()] = sprintf(
                '%s (ID: %d)',
                $group->getName(),
                $group->getId()
            );
        }

        if ($options === []) {
            return null;
        }

        return (string) select('<question>Select a store group to delete:</question>', $options);
    }
}
