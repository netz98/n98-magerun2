<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Exception;
use Faker\Factory as FakerFactory;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Model\View\Design;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateDummyCommand
 * @package N98\Magento\Command\Customer
 */
class CreateDummyCommand extends AbstractCustomerCommand
{
    private const SUPPORTED_LOCALES = [
        'en_US',
        'de_DE',
        'en_GB',
        'fr_FR',
        'it_IT',
        'es_AR',
        'de_AT',
        'cs_CZ',
        'ru_RU',
        'bg_BG',
        'sr_RS',
        'sr_Cyrl_RS',
        'sr_Latn_RS',
        'pl_PL',
        'sk_SK',
    ];

    /**
     * @var AccountManagementInterface
     */
    private AccountManagementInterface $accountManagement;

    /**
     * @var AppState
     */
    private AppState $appState;

    protected function configure()
    {
        $this
            ->setName('customer:create:dummy')
            ->addArgument('count', InputArgument::OPTIONAL, 'Count')
            ->addArgument('locale', InputArgument::OPTIONAL, 'Locale')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website')
            ->addOption(
                'with-addresses',
                null,
                InputOption::VALUE_NONE,
                'Create dummy billing/shipping addresses for each customer'
            )
            ->addOption(
                'print-password',
                null,
                InputOption::VALUE_NONE,
                'Print the generated password in the command line'
            )
            ->setDescription('Generate dummy customers. You can specify a count and a locale.')
            ->addFormatOption();
    }

    public function getHelp(): string
    {
        return <<<HELP
Supported Locales:

- cs_CZ
- ru_RU
- bg_BG
- en_US
- it_IT
- sr_RS
- sr_Cyrl_RS
- sr_Latn_RS
- pl_PL
- en_GB
- de_DE
- sk_SK
- fr_FR
- es_AR
- de_AT
HELP;
    }

    /**
     * @param AccountManagementInterface $accountManagement
     * @param AppState $appState
     */
    public function inject(
        AccountManagementInterface $accountManagement,
        AppState $appState
    ) {
        $this->accountManagement = $accountManagement;
        $this->appState = $appState;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $count = $input->getArgument('count');
        if ($count === null || $count === '') {
            $count = text('Amount of customers to generate', '1');
            if ($count === '') {
                $count = 1;
            }
        }
        $count = (int) $count;

        $locale = $input->getArgument('locale');
        if ($locale === null || $locale === '') {
            $locale = select(
                label: 'Locale (used to generate realistic localized names, addresses, postcodes, and phone numbers)',
                options: self::SUPPORTED_LOCALES,
                default: 'en_US'
            );
        }

        try {
            $faker = FakerFactory::create($locale);
        } catch (\InvalidArgumentException $e) {
            $faker = FakerFactory::create('en_US');
        }

        $website = $this->getHelperSet()->get('parameter')->askWebsite($input, $output);
        $withAddresses = $input->getOption('with-addresses');
        $printPassword = $input->getOption('print-password');
        $outputPlain = $input->getOption('format') === null;
        $table = [];
        $isError = false;

        for ($i = 0; $i < $count; $i++) {
            $email = $faker->email;
            $password = $printPassword ? $this->generateRandomPassword() : 'Password123!';
            $firstname = $faker->firstName;
            $lastname = $faker->lastName;

            // create new customer
            $customer = $this->getCustomer();
            $customer->setWebsiteId($website->getId());
            $customer->loadByEmail($email);

            if (!$customer->getId()) {
                $customer->setWebsiteId($website->getId());
                $customer->setEmail($email);
                $customer->setFirstname($firstname);
                $customer->setLastname($lastname);
                $customer->setStoreId($website->getDefaultGroup()->getDefaultStore()->getId());

                try {
                    try {
                        $this->appState->setAreaCode('frontend');
                    } catch (LocalizedException $e) {
                        if ($e->getMessage() !== 'Area code is already set') {
                            throw $e;
                        }
                    }

                    $this->appState->emulateAreaCode(
                        'frontend',
                        [$this, 'createCustomer'],
                        [$customer, $password, $withAddresses, $locale, $faker]
                    );

                    if ($outputPlain) {
                        if ($printPassword) {
                            $output->writeln(
                                sprintf(
                                    '<info>Customer <comment>%s</comment> successfully created with password <comment>%s</comment></info>',
                                    $email,
                                    \Symfony\Component\Console\Formatter\OutputFormatter::escape($password)
                                )
                            );
                        } else {
                            $output->writeln(
                                sprintf(
                                    '<info>Customer <comment>%s</comment> successfully created</info>',
                                    $email
                                )
                            );
                        }
                    } else {
                        $table[] = [
                            $email,
                            $password,
                            $firstname,
                            $lastname,
                        ];
                    }
                } catch (\Throwable $e) {
                    $isError = true;
                    $output->writeln('<error>' . $e->getMessage() . '</error>');
                }
            } elseif ($outputPlain) {
                $output->writeln('<info>Skipped existing customer <comment>' . $email . '</comment></info>');
            }
        }

        if (!$outputPlain) {
            $this->getHelper('table')
                ->setHeaders(['email', 'password', 'firstname', 'lastname'])
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return $isError ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $password
     * @param bool $withAddresses
     * @param string $locale
     * @param \Faker\Generator|null $faker
     * @throws LocalizedException
     */
    public function createCustomer($customer, $password, $withAddresses, $locale, $faker)
    {
        try {
            // Fix for proxy which does not respect "emulateAreaCode".
            // @see \Magento\Framework\Session\SessionManager::isSessionExists Hack to prevent session problems
            @session_start();

            /** @var Design $design */
            $design = $this->getObjectManager()->get(Design::class);
            $design->setArea('frontend');

            $customerData = $customer->getDataModel();

            if ($withAddresses && $faker !== null) {
                $countryId = 'US';
                if (preg_match('/_([A-Z]{2})/', $locale, $matches)) {
                    $countryId = $matches[1];
                }

                $addressFactory = $this->getObjectManager()->get('Magento\Customer\Api\Data\AddressInterfaceFactory');
                $address = $addressFactory->create();
                $address->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setStreet([$faker->streetAddress])
                    ->setCity($faker->city)
                    ->setCountryId($countryId)
                    ->setPostcode($faker->postcode)
                    ->setTelephone($faker->phoneNumber)
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true);

                // Pre-validate the address to prevent TypeErrors in MageOS/Magento 2.4.6+ validation crash
                /** @var \Magento\Framework\Validator\Factory $validatorFactory */
                $validatorFactory = $this->getObjectManager()->get('Magento\Framework\Validator\Factory');
                $addressValidator = $validatorFactory->createValidator('customer_address', 'save');

                /** @var \Magento\Customer\Model\AddressFactory $addressModelFactory */
                $addressModelFactory = $this->getObjectManager()->get('Magento\Customer\Model\AddressFactory');
                $addressModel = $addressModelFactory->create()->updateData($address);

                /** @var \Magento\Customer\Model\Config\Share $configShare */
                $configShare = $this->getObjectManager()->get('Magento\Customer\Model\Config\Share');
                if ($configShare->isWebsiteScope()) {
                    $addressModel->setStoreId($customer->getStoreId());
                }

                if (!$addressValidator->isValid($addressModel)) {
                    $errors = [];
                    foreach ($addressValidator->getMessages() as $field => $messages) {
                        $errors[] = sprintf('%s: %s', $field, implode(', ', (array) $messages));
                    }
                    throw new LocalizedException(
                        __('Address validation failed: %1', implode('; ', $errors))
                    );
                }

                $customerData->setAddresses([$address]);
            }

            $this->accountManagement->createAccount(
                $customerData,
                $password
            );
        } catch (LocalizedException $e) {
            if ($e->getRawMessage() !== 'Design config must have area and store.') {
                throw $e;
            }
        }
    }

    /**
     * Generate a random password that matches Magento 2 password rules.
     * Must contain uppercase, lowercase, digit, and special char.
     *
     * @return string
     */
    private function generateRandomPassword(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $caps = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nums = '0123456789';
        $syms = '!@#$%^&*()-_=+[]{}|;:,.?';

        $password = '';
        // Add one from each group
        $password .= $chars[rand(0, strlen($chars) - 1)];
        $password .= $caps[rand(0, strlen($caps) - 1)];
        $password .= $nums[rand(0, strlen($nums) - 1)];
        $password .= $syms[rand(0, strlen($syms) - 1)];

        // Fill the rest randomly
        $all = $chars . $caps . $nums . $syms;
        for ($i = 0; $i < 8; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }
}
