<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Website;

use function Laravel\Prompts\text;
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
     * @var WebsiteFactory
     */
    private $websiteFactory;

    protected function configure()
    {
        $this
            ->setName('sys:website:create')
            ->addArgument('code', InputArgument::OPTIONAL, 'Website code')
            ->addArgument('name', InputArgument::OPTIONAL, 'Website name')
            ->addOption(
                'default-group-id',
                null,
                InputOption::VALUE_REQUIRED,
                'ID of the default store group'
            )
            ->setDescription('Create a new website');
    }

    public function inject(WebsiteFactory $websiteFactory)
    {
        $this->websiteFactory = $websiteFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        if ($input->isInteractive() || $code === null || $code === '') {
            $code = text(
                '<question>Website code:</question>',
                default: (string) ($code ?? ''),
                validate: fn ($value) => $this->validateWebsiteCode($value)
            );
        }

        $codeValidationError = $this->validateWebsiteCode($code);
        if ($codeValidationError !== null) {
            throw new RuntimeException($codeValidationError);
        }

        $name = $input->getArgument('name');
        if ($input->isInteractive() || $name === null || $name === '') {
            $name = text(
                '<question>Website name:</question>',
                default: (string) ($name ?? ''),
                validate: fn ($value) => $value === '' ? 'Please enter a website name' : null
            );
        }

        $website = $this->websiteFactory->create();
        $website->setCode($code);
        $website->setName($name);

        $defaultGroupId = $input->getOption('default-group-id');
        if ($defaultGroupId !== null) {
            if (!ctype_digit((string) $defaultGroupId) || (int) $defaultGroupId < 1) {
                throw new RuntimeException('The default group ID must be a positive integer.');
            }

            $website->setDefaultGroupId((int) $defaultGroupId);
        }

        try {
            $website->save();
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }

        $output->writeln(
            sprintf(
                '<info>Successfully created website <comment>%s</comment> with ID: <comment>%d</comment></info>',
                $website->getCode(),
                $website->getId()
            )
        );

        return Command::SUCCESS;
    }

    private function validateWebsiteCode(string $code): ?string
    {
        if ($code === '') {
            return 'Please enter a website code';
        }

        if (strlen($code) > 32) {
            return 'Website code must not exceed 32 characters.';
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $code) !== 1) {
            return 'Website code may only contain letters (a-z), numbers (0-9) or underscore (_), '
                . 'and the first character must be a letter.';
        }

        return null;
    }
}
