<?php

namespace N98\Magento\Command\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class: AddAddressCommand
 */
class AddAddressCommand extends AbstractCustomerCommand
{
    /** @var CustomerRepositoryInterface */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    private State $state;

    /**
     * Method: configure
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('customer:add-address')
            ->setDescription('Adds an address to a customer')
            ->addArgument('email', InputArgument::REQUIRED, 'Customer email')
            ->addArgument('website', InputArgument::REQUIRED, 'Customer website')
            ->addOption('firstname', null, InputOption::VALUE_REQUIRED, 'First name')
            ->addOption('lastname', null, InputOption::VALUE_REQUIRED, 'Last name')
            ->addOption('street', null, InputOption::VALUE_REQUIRED, 'Street address')
            ->addOption('city', null, InputOption::VALUE_REQUIRED, 'City')
            ->addOption('country', null, InputOption::VALUE_REQUIRED, 'Country ID, e.g., US')
            ->addOption('postcode', null, InputOption::VALUE_REQUIRED, 'Postcode')
            ->addOption('telephone', null, InputOption::VALUE_REQUIRED, 'Telephone number')
            ->addOption('default-billing', null, InputOption::VALUE_NONE, 'Use as default billing address')
            ->addOption('default-shipping', null, InputOption::VALUE_NONE, 'Use as default shipping address');
    }

    /**
     * Method: inject
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @return void
     */
    public function inject(
        CustomerRepositoryInterface $customerRepository,
        State $state,
    ) {
        $this->customerRepository = $customerRepository;
        $this->state = $state;
    }

    /**
     * Method: execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $isError = false;

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');

        // Email is a required argument, so we check and ask if it's not provided
        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Please enter the customer\'s email: ');
            $email = $questionHelper->ask($input, $output, $question);
        }

        $website = $this->getHelperSet()->get('parameter')->askWebsite($input, $output);

        // Collecting options with possibility of interactive input
        $options = ['firstname', 'lastname', 'street', 'city', 'country', 'postcode', 'telephone'];
        $data = [];
        foreach ($options as $option) {
            $value = $input->getOption($option);
            if (!$value) {
                $question = new Question("Please enter the customer's $option: ");
                $value = $questionHelper->ask($input, $output, $question);
            }
            $data[$option] = $value;
        }

        // Handling boolean options for default billing and shipping addresses
        $defaultOptions = ['default-billing', 'default-shipping'];
        foreach ($defaultOptions as $option) {
            $value = $input->getOption($option);
            if (null === $value) {
                $question = new ConfirmationQuestion("Set address as customer's $option? (yes/no): ", false);
                $value = $questionHelper->ask($input, $output, $question);
            }
            $data[$option] = $value;
        }

        // Now, load the customer by email within the context of the website
        try {
            $customer = $this->customerRepository->get($email, $website->getId());
        } catch (NoSuchEntityException $e) {
            $websiteCode = $website->getCode();
            $output->writeln("<error>Customer with email '$email' not found in website '$websiteCode'.</error>");
            return Command::FAILURE;
        }

        try {
            $createAddress = function () use ($data, $customer, $output, $email) {
                return $this->createAddress($data, $customer, $output, $email);
            };

            $this->state->setAreaCode(Area::AREA_FRONTEND);
            $isError = $this->state->emulateAreaCode(Area::AREA_FRONTEND, $createAddress);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return $isError ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param array $data
     * @param $customer
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param mixed $email
     * @param bool $isError
     * @return bool
     */
    protected function createAddress(array $data, $customer, OutputInterface $output, mixed $email): bool
    {
        $isError = false;

        /** @var AddressInterfaceFactory $addressFactory */
        $addressFactory = $this->getObjectManager()->get(AddressInterfaceFactory::class);
        $address = $addressFactory->create();
        $address->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setStreet([$data['street']])
            ->setCity($data['city'])
            ->setCountryId($data['country'])
            ->setPostcode($data['postcode'])
            ->setTelephone($data['telephone'])
            ->setIsDefaultBilling($data['default-billing'])
            ->setIsDefaultShipping($data['default-shipping']);

        $mergedAddresses = array_merge($customer->getAddresses(), [$address]);

        $customer->setAddresses($mergedAddresses);

        try {
            $this->customerRepository->save($customer);
            $output->writeln('<info>Address added successfully to customer ' . $email . '.</info>');
        } catch (Exception $e) {
            $isError = true;
            $output->writeln('<error>Error adding address: ' . $e->getMessage() . '</error>');
        }

        return $isError;
    }
}
