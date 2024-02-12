<?php

namespace AddCustomerAddress\Command;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use N98\Magento\Command\Customer\AbstractCustomerCommand;
use Symfony\Component\Console\Command\Command;
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
            ->addOption('default_billing', null, InputOption::VALUE_OPTIONAL, 'Use as default billing address')
            ->addOption('default_shipping', null, InputOption::VALUE_OPTIONAL, 'Use as default shipping address');
    }

    /**
     * Method: inject
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @return void
     */
    public function inject(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Method: execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
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

        // Handling optional options for default billing and shipping addresses
        $defaultOptions = ['default_billing', 'default_shipping'];
        foreach ($defaultOptions as $option) {
            $value = $input->getOption($option);
            if (null === $value) {
                $question = new ConfirmationQuestion("Is this address the customer's $option? (yes/no): ");
                $value = $questionHelper->ask($input, $output, $question);
            }
            $data[$option] = $value;
        }

        // Now, load the customer by email within the context of the website
        try {
            $customer = $this->customerRepository->get($email, $website->getId());
        } catch (NoSuchEntityException $e) {
            $output->writeln("<error>Customer with email '$email' not found in website '$website'.</error>");
            return Command::FAILURE;
        }

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
            ->setIsDefaultBilling($data['default_billing'])
            ->setIsDefaultShipping($data['default_shipping']);

        $mergedAddresses = array_merge($customer->getAddresses(), [$address]);

        $customer->setAddresses($mergedAddresses);

        try {
            $this->customerRepository->save($customer);
            $output->writeln('<info>Address added successfully to customer ' . $email . '.</info>');
        } catch (Exception $e) {
            $isError = true;
            $output->writeln('<error>Error adding address: ' . $e->getMessage() . '</error>');
        }

        return $isError ? Command::FAILURE : Command::SUCCESS;
    }
}
