<?php

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\State;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends AbstractCustomerCommand
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var State
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
     */
    public function inject(
        AccountManagementInterface $accountManagement,
        State $appState
    ) {
        $this->accountManagement = $accountManagement;
        $this->appState = $appState;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $dialog = $this->getHelperSet()->get('dialog');

        // Email
        $email = $this->getHelperSet()->get('parameter')->askEmail($input, $output);

        // Password
        $password = $this->getHelperSet()->get('parameter')->askPassword($input, $output, 'password', false);

        // Firstname
        if (($firstname = $input->getArgument('firstname')) == null) {
            $firstname = $dialog->ask($output, '<question>Firstname:</question>');
        }

        // Lastname
        if (($lastname = $input->getArgument('lastname')) == null) {
            $lastname = $dialog->ask($output, '<question>Lastname:</question>');
        }

        $website = $this->getHelperSet()->get('parameter')->askWebsite($input, $output);

        // create new customer
        $customer = $this->getCustomer();
        $customer->setWebsiteId($website->getId());
        $customer->loadByEmail($email);

        $outputPlain = $input->getOption('format') === null;

        $table = array();
        $isError = false;

        if (!$customer->getId()) {
            $customer->setWebsiteId($website->getId());
            $customer->setEmail($email);
            $customer->setFirstname($firstname);
            $customer->setLastname($lastname);

            try {
                $this->appState->emulateAreaCode('frontend', function () use ($customer, $password) {
                    $this->accountManagement->createAccount(
                        $customer->getDataModel(),
                        $password
                    );
                });

                if ($outputPlain) {
                    $output->writeln(
                        sprintf(
                            '<info>Customer <comment>%s</comment> successfully created</info>',
                            $email
                        )
                    );
                } else {
                    $table[] = array(
                        $email, $password, $firstname, $lastname,
                    );
                }

            } catch (\Exception $e) {
                $isError = true;
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        } else {
            if ($outputPlain) {
                $output->writeln('<warning>Customer ' . $email . ' already exists</warning>');
            }
        }

        if (!$outputPlain) {
            $this->getHelper('table')
                ->setHeaders(array('email', 'password', 'firstname', 'lastname'))
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return $isError ? 1 : 0;
    }
}
