<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\StoreGroup;

use function Laravel\Prompts\text;
use Magento\Store\Model\GroupFactory;
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
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    protected function configure()
    {
        $this
            ->setName('sys:store-group:create')
            ->addArgument('name', InputArgument::OPTIONAL, 'Store group name')
            ->addOption('website-id', null, InputOption::VALUE_REQUIRED, 'Website ID')
            ->addOption('website-code', null, InputOption::VALUE_REQUIRED, 'Website code')
            ->addOption('root-category-id', null, InputOption::VALUE_REQUIRED, 'Root category ID')
            ->addOption('default-store-id', null, InputOption::VALUE_REQUIRED, 'ID of the default store')
            ->setDescription('Create a new store group');
    }

    public function inject(GroupFactory $groupFactory, WebsiteFactory $websiteFactory)
    {
        $this->groupFactory = $groupFactory;
        $this->websiteFactory = $websiteFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if ($name === null || $name === '') {
            $name = text(
                '<question>Store group name:</question>',
                validate: fn ($value) => $value === '' ? 'Please enter a store group name' : null
            );
        }

        $websiteId = $input->getOption('website-id');
        $websiteCode = $input->getOption('website-code');
        if ($websiteId !== null && $websiteCode !== null) {
            throw new RuntimeException('Specify either --website-id or --website-code, not both.');
        }

        if ($websiteId === null && $websiteCode === null) {
            throw new RuntimeException('Either --website-id or --website-code is required.');
        }

        if ($websiteId !== null && (!ctype_digit((string) $websiteId) || (int) $websiteId < 1)) {
            throw new RuntimeException('The website ID must be a positive integer.');
        }

        $website = $this->websiteFactory->create();
        if ($websiteId !== null) {
            $website->load((int) $websiteId);
        } else {
            $website->load($websiteCode, 'code');
        }

        if (!$website->getId()) {
            $identifier = $websiteId ?? $websiteCode;
            throw new RuntimeException(sprintf('Website with code or ID "%s" does not exist.', $identifier));
        }

        $rootCategoryId = $input->getOption('root-category-id');
        if ($rootCategoryId === null || !ctype_digit((string) $rootCategoryId) || (int) $rootCategoryId < 1) {
            throw new RuntimeException('The root category ID must be a positive integer.');
        }

        $defaultStoreId = $input->getOption('default-store-id');
        if ($defaultStoreId !== null && (!ctype_digit((string) $defaultStoreId) || (int) $defaultStoreId < 1)) {
            throw new RuntimeException('The default store ID must be a positive integer.');
        }

        $group = $this->groupFactory->create();
        $group->setName($name);
        $group->setWebsiteId((int) $website->getId());
        $group->setRootCategoryId((int) $rootCategoryId);
        if ($defaultStoreId !== null) {
            $group->setDefaultStoreId((int) $defaultStoreId);
        }

        try {
            $group->save();
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }

        $output->writeln(
            sprintf(
                '<info>Successfully created store group <comment>%s</comment> with ID: <comment>%d</comment></info>',
                $group->getName(),
                $group->getId()
            )
        );

        return Command::SUCCESS;
    }
}
