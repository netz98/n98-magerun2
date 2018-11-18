<?php

namespace N98\Magento\Command\Customer;

use Exception;
use Magento\Framework\App\Area;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePasswordCommand extends AbstractCustomerCommand
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry $customerRegistry
     */
    private $customerRegistry;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    protected function configure()
    {
        $this
            ->setName('customer:change-password')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website of the customer')
            ->setDescription('Changes the password of a customer.')
        ;

        $help = <<<HELP
- Website parameter must only be given if more than one websites are available.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function inject(
        \Magento\Framework\App\State $state,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->state = $state;
        $this->customerRepository = $customerRepository;
        $this->customerResource = $customerResource;
        $this->customerRegistry = $customerRegistry;
        $this->encryptor = $encryptor;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $dialog = $this->getHelperSet()->get('dialog');
            $email = $this->getHelper('parameter')->askEmail($input, $output);

            // Password
            if (($password = $input->getArgument('password')) == null) {
                $password = $dialog->ask($output, '<question>Password:</question>');
            }

            $website = $this->getHelper('parameter')->askWebsite($input, $output);

            $changePassword = function () use ($email, $website, $password) {
                // @see \Magento\Framework\Session\SessionManager::isSessionExists Hack to prevent session problems
                @session_start();

                // Fix for proxy which does not respect "emulateAreaCode".
                /** @var \Magento\Theme\Model\View\Design $design */
                $design = $this->getObjectManager()->get(\Magento\Theme\Model\View\Design::class);
                $design->setArea('frontend');

                $customer = $this->customerRegistry->retrieveByEmail($email, $website->getId());
                $passwordHash = $this->encryptor->getHash($password, true);

                if ($customer->getId()) {
                    $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());

                    $customer->setRpToken($passwordHash ? null : $customerSecure->getRpToken());
                    $customer->setRpTokenCreatedAt($passwordHash ? null : $customerSecure->getRpTokenCreatedAt());
                    $customer->setPasswordHash($passwordHash ?: $customerSecure->getPasswordHash());

                    $customer->setFailuresNum($customerSecure->getFailuresNum());
                    $customer->setFirstFailure($customerSecure->getFirstFailure());
                    $customer->setLockExpires($customerSecure->getLockExpires());
                } elseif ($passwordHash) {
                    $customer->setPasswordHash($passwordHash);
                }

                $this->customerResource->save($customer);

                if ($passwordHash && $customer->getId()) {
                    $this->customerRegistry->remove($customer->getId());
                }
            };

            try {
                $this->state->setAreaCode(Area::AREA_FRONTEND);
                $this->state->emulateAreaCode(Area::AREA_FRONTEND, $changePassword);

                $output->writeln('<info>Password successfully changed</info>');
            } catch (Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }
    }
}
