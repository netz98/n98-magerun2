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
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Model\View\Design;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateDummyCommand
 * @package N98\Magento\Command\Customer
 */
class CreateDummyCommand extends AbstractCustomerCommand
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var AppState
     */
    private $appState;

    protected function configure()
    {
        $this
            ->setName('customer:create:dummy')
            ->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Amount of customers to generate', 1)
            ->addOption('locale', null, InputOption::VALUE_OPTIONAL, 'Locale', 'en_US')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('Creates dummy customers');
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $count = (int) $input->getOption('count');
        $locale = $input->getOption('locale');
        $faker = FakerFactory::create($locale);

        $website = $this->getHelperSet()->get('parameter')->askWebsite($input, $output);

        $outputPlain = $input->getOption('format') === null;
        $table = [];
        $isError = false;

        for ($i = 0; $i < $count; $i++) {
            $email = $faker->email;
            $password = 'password123';
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
                        [$customer, $password]
                    );

                    if ($outputPlain) {
                        $output->writeln(
                            sprintf(
                                '<info>Customer <comment>%s</comment> successfully created</info>',
                                $email
                            )
                        );
                    } else {
                        $table[] = [
                            $email,
                            $password,
                            $firstname,
                            $lastname,
                        ];
                    }
                } catch (Exception $e) {
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
     * @param string $customer
     * @param string $password
     * @throws LocalizedException
     */
    public function createCustomer($customer, $password)
    {
        try {
            // Fix for proxy which does not respect "emulateAreaCode".

            // @see \Magento\Framework\Session\SessionManager::isSessionExists Hack to prevent session problems
            @session_start();

            /** @var Design $design */
            $design = $this->getObjectManager()->get(Design::class);
            $design->setArea('frontend');
            $this->accountManagement->createAccount(
                $customer->getDataModel(),
                $password
            );
        } catch (LocalizedException $e) {
            if ($e->getRawMessage() !== 'Design config must have area and store.') {
                throw $e;
            }
        }
    }
}
