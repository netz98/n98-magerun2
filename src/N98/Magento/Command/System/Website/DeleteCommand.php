<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Website;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\WebsiteFactory;
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
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var Registry
     */
    private $registry;

    protected function configure()
    {
        $this
            ->setName('sys:website:delete')
            ->addArgument('code', InputArgument::OPTIONAL, 'Website code or ID')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Delete without confirmation')
            ->setDescription('Delete an existing website');
    }

    public function inject(WebsiteFactory $websiteFactory, Registry $registry)
    {
        $this->websiteFactory = $websiteFactory;
        $this->registry = $registry;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('code');
        if ($identifier === null) {
            $identifier = $this->selectWebsite();
        }

        if ($identifier === null) {
            $output->writeln('<info>No websites found.</info>');

            return Command::SUCCESS;
        }

        if ((string) $identifier === WebsiteInterface::ADMIN_CODE) {
            throw new RuntimeException('The admin website cannot be deleted.');
        }

        $website = $this->websiteFactory->create()->load($identifier, 'code');

        if (!$website->getId() && ctype_digit((string) $identifier)) {
            $website = $this->websiteFactory->create()->load((int) $identifier);
        }

        if ($website->getCode() === WebsiteInterface::ADMIN_CODE) {
            throw new RuntimeException('The admin website cannot be deleted.');
        }

        if (!$website->getId()) {
            throw new RuntimeException(sprintf('Website with code or ID "%s" does not exist.', $identifier));
        }

        if ($website->getIsDefault()) {
            throw new RuntimeException('The default website cannot be deleted.');
        }

        if (!$input->getOption('force') && !confirm(
            sprintf(
                '<question>Are you sure you want to delete website "%s" (ID: %d)?</question>',
                $website->getCode(),
                $website->getId()
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
            $website->delete();
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        } finally {
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', $isSecure);
        }

        $output->writeln(
            sprintf(
                '<info>Successfully deleted website <comment>%s</comment> with ID: <comment>%d</comment></info>',
                $website->getCode(),
                $website->getId()
            )
        );

        return Command::SUCCESS;
    }

    /**
     * @return string|null
     */
    private function selectWebsite(): ?string
    {
        $websites = $this->websiteFactory->create()->getCollection()->getItems();
        $options = [];

        foreach ($websites as $website) {
            if ($website->getCode() === WebsiteInterface::ADMIN_CODE) {
                continue;
            }

            $options[$website->getCode()] = sprintf(
                '%s (ID: %d)',
                $website->getCode(),
                $website->getId()
            );
        }

        if ($options === []) {
            return null;
        }

        return select('<question>Select a website to delete:</question>', $options);
    }
}
