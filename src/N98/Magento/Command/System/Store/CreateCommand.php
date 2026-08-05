<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Store;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends AbstractMagentoCommand
{
    private $storeFactory;

    private $groupFactory;

    private $websiteFactory;

    protected function configure()
    {
        $this
            ->setName('sys:store:create')
            ->addArgument('code', InputArgument::OPTIONAL, 'Store code')
            ->addArgument('name', InputArgument::OPTIONAL, 'Store name')
            ->addOption('group-id', null, InputOption::VALUE_REQUIRED, 'Store group ID')
            ->addOption('group-code', null, InputOption::VALUE_REQUIRED, 'Store group code')
            ->addOption('website-id', null, InputOption::VALUE_REQUIRED, 'Use the website default store group')
            ->addOption('is-active', null, InputOption::VALUE_REQUIRED, 'Whether the store is active (1 or 0)')
            ->setDescription('Create a new store view');
    }

    public function inject(StoreFactory $storeFactory, GroupFactory $groupFactory, WebsiteFactory $websiteFactory)
    {
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
        $this->websiteFactory = $websiteFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        if ($input->isInteractive() || $code === null || $code === '') {
            $code = text(
                '<question>Store code:</question>',
                default: (string) ($code ?? ''),
                validate: fn ($value) => $this->validateStoreCode($value)
            );
        }

        $codeValidationError = $this->validateStoreCode($code);
        if ($codeValidationError !== null) {
            throw new RuntimeException($codeValidationError);
        }

        $name = $input->getArgument('name');
        if ($input->isInteractive() || $name === null || $name === '') {
            $name = text(
                '<question>Store name:</question>',
                default: (string) ($name ?? ''),
                validate: fn ($value) => $value === '' ? 'Please enter a store name' : null
            );
        }

        $groupId = $this->resolveGroupId($input);
        $store = $this->storeFactory->create();
        $store->setCode($code);
        $store->setName($name);
        $store->setStoreGroupId($groupId);

        $isActive = $input->getOption('is-active');
        if ($isActive !== null) {
            if (!in_array((string) $isActive, ['0', '1'], true)) {
                throw new RuntimeException('The is-active value must be 0 or 1.');
            }

            $store->setIsActive((int) $isActive);
        }

        try {
            $store->save();
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }

        $output->writeln(
            sprintf(
                '<info>Successfully created store <comment>%s</comment> with ID: <comment>%d</comment></info>',
                $store->getCode(),
                $store->getId()
            )
        );

        return Command::SUCCESS;
    }

    private function resolveGroupId(InputInterface $input): int
    {
        if ($input->isInteractive()) {
            $groupId = $this->selectGroup();
        } else {
            $groupId = $input->getOption('group-id');
            $groupCode = $input->getOption('group-code');
            $websiteId = $input->getOption('website-id');
            $specified = array_filter([$groupId, $groupCode, $websiteId], static fn ($value) => $value !== null);
            if (count($specified) > 1) {
                throw new RuntimeException('Specify only one of --group-id, --group-code, or --website-id.');
            }

            if ($groupId !== null) {
                $this->validatePositiveInteger($groupId, 'The store group ID must be a positive integer.');
                $group = $this->groupFactory->create()->load((int) $groupId);
            } elseif ($groupCode !== null) {
                $group = $this->groupFactory->create()->load($groupCode, 'code');
            } elseif ($websiteId !== null) {
                $this->validatePositiveInteger($websiteId, 'The website ID must be a positive integer.');
                $website = $this->websiteFactory->create()->load((int) $websiteId);
                if (!$website->getId()) {
                    throw new RuntimeException(sprintf('Website with ID "%s" does not exist.', $websiteId));
                }

                $group = $this->groupFactory->create()->load((int) $website->getDefaultGroupId());
            } else {
                throw new RuntimeException(
                    'A store group is required. Specify --group-id, --group-code, or --website-id.'
                );
            }

            $groupId = $group->getId();
        }

        if (empty($groupId)) {
            throw new RuntimeException('No store groups found.');
        }

        $group = $this->groupFactory->create()->load((int) $groupId);
        if (!$group->getId()) {
            throw new RuntimeException(sprintf('Store group with ID "%s" does not exist.', $groupId));
        }

        return (int) $group->getId();
    }

    private function selectGroup(): ?string
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

        $selected = (string) select('<question>Select a store group:</question>', $options);
        $selectedId = array_search($selected, $options, true);

        return $selectedId === false ? $selected : (string) $selectedId;
    }

    private function validatePositiveInteger($value, string $message): void
    {
        if (!ctype_digit((string) $value) || (int) $value < 1) {
            throw new RuntimeException($message);
        }
    }

    private function validateStoreCode(string $code): ?string
    {
        if ($code === '') {
            return 'Please enter a store code';
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $code) !== 1) {
            return 'Store code may only contain letters (a-z), numbers (0-9) or underscore (_), '
                . 'and the first character must be a letter.';
        }

        return null;
    }
}
