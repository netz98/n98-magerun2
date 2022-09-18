<?php

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class CreateCommand
 * @package N98\Magento\Command\Customer
 */
class CreateCommand extends AbstractCustomerCommand
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
            ->setName('customer:create')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addArgument('firstname', InputArgument::OPTIONAL, 'Firstname')
            ->addArgument('lastname', InputArgument::OPTIONAL, 'Lastname')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website')
            ->addArgument('additionalFields', InputArgument::IS_ARRAY, 'Additional fields, specifiy as field_name1 value2 field_name2 value2')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('Creates a new customer/user for shop frontend.');
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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return 1;
        }

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');

        // Email
        $email = $this->getHelperSet()->get('parameter')->askEmail($input, $output);

        // Password
        $password = $input->getArgument('password');
        if ($password === null) {
            $question = new Question('<question>Password:</question> ');
            $question->setHidden(true);
            $password = $questionHelper->ask($input, $output, $question);
        }

        // Firstname
        $firstname = $input->getArgument('firstname');
        if ($firstname === null) {
            $question = new Question('<question>Firstname:</question> ');
            $firstname = $questionHelper->ask($input, $output, $question);
        }

        // Lastname
        $lastname = $input->getArgument('lastname');
        if ($lastname === null) {
            $question = new Question('<question>Lastname:</question> ');
            $lastname = $questionHelper->ask($input, $output, $question);
        }

        $website = $this->getHelperSet()->get('parameter')->askWebsite($input, $output);

        try {
            $additionalFields = $this->getAdditionalFields($input);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        // create new customer
        $customer = $this->getCustomer();
        $customer->setWebsiteId($website->getId());
        $customer->loadByEmail($email);

        $customer->addData($additionalFields);

        $outputPlain = $input->getOption('format') === null;

        $table = [];

        $isError = false;

        if (!$customer->getId()) {
            $customer->setWebsiteId($website->getId());
            $customer->setEmail($email);
            $customer->setFirstname($firstname);
            $customer->setLastname($lastname);
            $customer->setStoreId($website->getDefaultGroup()->getDefaultStore()->getId());

            try {
                $this->appState->setAreaCode('frontend');
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
            } catch (\Exception $e) {
                $isError = true;
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        } elseif ($outputPlain) {
            $output->writeln('<warning>Customer ' . $email . ' already exists</warning>');
        }

        if (!$outputPlain) {
            $this->getHelper('table')
                ->setHeaders(['email', 'password', 'firstname', 'lastname'])
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return $isError ? 1 : 0;
    }

    /**
     * @param string $customer
     * @param string $password
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomer($customer, $password)
    {
        try {
            // Fix for proxy which does not respect "emulateAreaCode".

            // @see \Magento\Framework\Session\SessionManager::isSessionExists Hack to prevent session problems
            @session_start();

            /** @var \Magento\Theme\Model\View\Design $design */
            $design = $this->getObjectManager()->get(\Magento\Theme\Model\View\Design::class);
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

    private function getAdditionalFields($input)
    {
        $additionalFields = $input->getArgument('additionalFields');

        if (count($additionalFields) == 0) {
            return [];
        }

        if (count($additionalFields) % 2 !== 0) {
            throw new \Exception('Additional fields must be formated as name1 value2 name2 value2, uneven paramater count specified');
        }

        $result = [];
        foreach (range(0, count($additionalFields) / 2 - 1) as $index) {
            $result[$additionalFields[$index * 2]] = $additionalFields[$index * 2 + 1];
        }

        return $result;
    }
}
