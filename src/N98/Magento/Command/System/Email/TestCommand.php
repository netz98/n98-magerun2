<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\System\Email;

use function Laravel\Prompts\text;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends AbstractMagentoCommand
{
    private const DEFAULT_TEMPLATE = 'contact_email_email_template';

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    public function inject(
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation
    ): void {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
    }

    protected function configure(): void
    {
        $this
            ->setName('sys:email:test')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Recipient email address (prompted for if omitted)')
            ->addOption(
                'from',
                null,
                InputOption::VALUE_REQUIRED,
                'Sender email address (defaults to the store\'s general contact email)'
            )
            ->addOption(
                'cc',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Additional cc email address (can be used multiple times)',
                []
            )
            ->addOption('store', null, InputOption::VALUE_REQUIRED, 'Store code or id')
            ->setDescription('Sends a test email through Magento\'s mail transport for deliverability testing');

        $this->setHelp(
            <<<HELP
Sends a minimal test email through Magento's own mail transport, without creating
any customer accounts or other test data. This is useful for checking email
deliverability with tools like mail-tester.com, and for verifying that SMTP
settings are correctly configured for a given store view.

The email re-uses the "Contact Form" template shipped with Magento, since it does
not require any additional data to be set up.

If --to is omitted, you will be prompted for it interactively.

Usage:

    n98-magerun2 sys:email:test
    n98-magerun2 sys:email:test --to=you@example.com
    n98-magerun2 sys:email:test --to=you@example.com --store=2
    n98-magerun2 sys:email:test --to=you@example.com --from=sender@example.com --cc=cc1@example.com --cc=cc2@example.com
HELP
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $to = $input->getOption('to');
        if ($to === null || $to === '') {
            $to = text(
                '<question>Recipient email address:</question>',
                validate: fn ($value) => $this->validateRequiredEmail($value)
            );
        }

        // In non-interactive mode (e.g. missing --to and no TTY), the prompt above cannot ask
        // and just returns an empty default, so this check still needs to catch that case.
        if (!is_string($to) || $to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $output->writeln('<error>Please provide a valid recipient email address with --to</error>');

            return Command::FAILURE;
        }

        $from = $input->getOption('from');
        if ($from !== null && !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $output->writeln('<error>The --from email address is not valid</error>');

            return Command::FAILURE;
        }

        $ccList = (array) $input->getOption('cc');
        foreach ($ccList as $cc) {
            if (!filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                $output->writeln(sprintf('<error>The --cc email address "%s" is not valid</error>', $cc));

                return Command::FAILURE;
            }
        }

        try {
            $store = $this->storeManager->getStore($input->getOption('store'));
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }

        if ($from !== null) {
            $fromEmail = $from;
            $fromName = 'n98-magerun2';
        } else {
            $fromEmail = (string) $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
            $fromName = (string) $this->scopeConfig->getValue(
                'trans_email/ident_general/name',
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
        }

        if ($fromEmail === '') {
            $output->writeln(
                '<error>Could not determine a sender email address for this store. Use --from to specify one.</error>'
            );

            return Command::FAILURE;
        }

        $templateId = (string) $this->scopeConfig->getValue(
            'contact/email/email_template',
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        if ($templateId === '') {
            $templateId = self::DEFAULT_TEMPLATE;
        }

        $variables = [
            'data' => [
                'name' => $fromName !== '' ? $fromName : 'n98-magerun2',
                'email' => $fromEmail,
                'telephone' => '',
                'comment' => sprintf(
                    'This is a test email sent by "n98-magerun2 sys:email:test" on %s for store "%s" (%s) '
                    . 'to check email deliverability.',
                    date('Y-m-d H:i:s'),
                    $store->getName(),
                    $store->getCode()
                ),
            ],
        ];

        $this->inlineTranslation->suspend();
        try {
            $transportBuilder = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $store->getId(),
                    ]
                )
                ->setTemplateVars($variables)
                ->setFromByScope(['email' => $fromEmail, 'name' => $fromName ?: 'n98-magerun2'], $store->getId())
                ->addTo($to)
                ->setReplyTo($fromEmail, $fromName !== '' ? $fromName : null);

            foreach ($ccList as $cc) {
                $transportBuilder->addCc($cc);
            }

            $transportBuilder->getTransport()->sendMessage();
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Could not send test email: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        } finally {
            $this->inlineTranslation->resume();
        }

        $output->writeln(
            sprintf(
                '<info>Test email sent to <comment>%s</comment> from <comment>%s</comment> '
                . 'for store <comment>%s</comment></info>',
                $to,
                $fromEmail,
                $store->getCode()
            )
        );

        if ($ccList !== []) {
            $output->writeln(sprintf('<info>Cc: <comment>%s</comment></info>', implode(', ', $ccList)));
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $value
     * @return string|null
     */
    private function validateRequiredEmail(string $value): ?string
    {
        if ($value === '') {
            return 'Please enter a recipient email address';
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address';
        }

        return null;
    }
}
