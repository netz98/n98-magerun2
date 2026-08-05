<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Store;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use Magento\Framework\Registry;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends AbstractMagentoCommand
{
    private $storeFactory;

    private $groupFactory;

    private $registry;

    protected function configure()
    {
        $this
            ->setName('sys:store:delete')
            ->addArgument('code', InputArgument::OPTIONAL, 'Store code or ID')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Delete without confirmation')
            ->setDescription('Delete an existing store view');
    }

    public function inject(StoreFactory $storeFactory, GroupFactory $groupFactory, Registry $registry)
    {
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
        $this->registry = $registry;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('code');
        if ($identifier === null) {
            $identifier = $this->selectStore();
        }

        if ($identifier === null) {
            $output->writeln('<info>No stores found.</info>');

            return Command::SUCCESS;
        }

        $store = $this->storeFactory->create()->load($identifier, 'code');
        if (!$store->getId() && ctype_digit((string) $identifier)) {
            $store = $this->storeFactory->create()->load((int) $identifier);
        }

        if (!$store->getId()) {
            throw new RuntimeException(sprintf('Store with code or ID "%s" does not exist.', $identifier));
        }

        $group = $this->groupFactory->create()->load((int) $store->getStoreGroupId());
        if ((int) $group->getDefaultStoreId() === (int) $store->getId()) {
            throw new RuntimeException('The default store of a store group cannot be deleted.');
        }

        if (!$input->getOption('force') && !confirm(
            sprintf(
                '<question>Are you sure you want to delete store "%s" (ID: %d)?</question>',
                $store->getCode(),
                $store->getId()
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
            $store->delete();
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        } finally {
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', $isSecure);
        }

        $output->writeln(
            sprintf(
                '<info>Successfully deleted store <comment>%s</comment> with ID: <comment>%d</comment></info>',
                $store->getCode(),
                $store->getId()
            )
        );

        return Command::SUCCESS;
    }

    private function selectStore(): ?string
    {
        $options = [];
        foreach ($this->storeFactory->create()->getCollection()->getItems() as $store) {
            $options[(string) $store->getId()] = sprintf(
                '%s (ID: %d, code: %s)',
                $store->getName(),
                $store->getId(),
                $store->getCode()
            );
        }

        if ($options === []) {
            return null;
        }

        $selected = (string) select('<question>Select a store to delete:</question>', $options);
        $selectedId = array_search($selected, $options, true);

        return $selectedId === false ? $selected : (string) $selectedId;
    }
}
