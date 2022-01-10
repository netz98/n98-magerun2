<?php

namespace N98\Magento\Command\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection\Interceptor as CustomerCollection;
use Magento\Framework\App\State\Proxy as AppState;
use Magento\Framework\Registry;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class DeleteCommand
 * @package N98\Magento\Command\Customer
 */
class DeleteCommand extends AbstractCustomerCommand
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('customer:delete')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Customer Id or email')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email')
            ->addOption('firstname', null, InputOption::VALUE_OPTIONAL, 'Firstname')
            ->addOption('lastname', null, InputOption::VALUE_OPTIONAL, 'Lastname')
            ->addOption('website', null, InputOption::VALUE_OPTIONAL, 'Website')
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'Force delete')
            ->addOption('all', 'a', InputOption::VALUE_OPTIONAL, 'Delete all customers')
            ->addOption('range', 'r', InputOption::VALUE_OPTIONAL, 'Delete a range of customers by Id')
            ->addOption('fuzzy', null, InputOption::VALUE_OPTIONAL, 'Fuzziness')
            ->setDescription('Deletes a customer/user for shop frontend by given options by matching or fuzzy search and/or range.');
    }

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param AppState                    $appState
     * @param Registry                    $registry
     */
    public function inject(
        CustomerRepositoryInterface $customerRepository,
        AppState                    $appState,
        Registry                    $registry
    ) {
        $this->customerRepository = $customerRepository;
        $this->appState = $appState;
        $this->registry = $registry;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return 1;
        }

        $this->input = $input;
        $this->output = $output;

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');

        // defaults
        $range = $all = false;
        $filterType = 'eq';
        $filterPrefix = '';
        $filterPostfix = '';
        $filterAttributes = [];

        // arguments
        $id = $this->input->getOption('id');
        $email = $this->input->getOption('email');
        $firstname = $this->input->getOption('firstname');
        $lastname = $this->input->getOption('lastname');
        $website = $this->input->getOption('website');

        // options
        $range = $this->input->getOption('range');
        $all = $this->input->getOption('all');
        $fuzzy = $this->input->getOption('fuzzy');

        // Get args required
        // we need at least:
        //      customerId
        //      OR
        //      websiteId
        //          AND
        //              email
        //              OR
        //              firstname and lastname
        //      OR
        //      range
        //      OR
        //      all
        if (!($id || ($website && ($email || ($lastname && $firstname)))) && !($range) && !($all) && !($fuzzy)) {

            // Delete more than one customer ?
            $question = new Question('<question>Delete more than 1 customer?</question> ');
            $batchDelete = $questionHelper->ask($input, $output, $question);

            if ($batchDelete) {
                // Batch deletion
                $all = $input->getOption('all');
                if ($all === null) {
                    $question = new Question('<question>Delete all customers?:</question> ');
                    $all = $questionHelper->ask($input, $output, $question);
                }

                if (!$all) {
                    $range = $input->getOption('range');
                    if ($all === null) {
                        $question = new Question('<question>Delete a range of customers?</question> ');
                        $range = $questionHelper->ask($input, $output, $question);
                    }

                    if (!$range) {
                        // Nothing to do
                        $this->output->writeln('<error>Finished nothing to do</error>');
                        return false;
                    }
                }
            }
        }

        if ($id) {
            // Single customer deletion without fuzziness
            // get customer by one of 'id' or 'email' or 'firstname' and 'lastname'
            try {
                $customer = $this->getCustomerById($id);
            } catch (Exception $e) {
                $this->output->writeln('<error>No customer found!</error>');
                return false;
            }

            if ($this->shouldRemove($questionHelper, $input, $output)) {
                $isSecure = $this->registry->registry('isSecureArea');
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', true);
                $this->customerRepository->delete($customer);
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', $isSecure);
            } else {
                $this->output->writeln('<error>Aborting delete</error>');
            }
        } else {
            if ($fuzzy) {
                $filterType = 'like';
                $filterPrefix = '%';
                $filterPostfix = '%';
            }

            if ($website) {
                $filterAttributes[] = ['attribute' => 'website', $filterType => $website];
            }
            if ($email) {
                $filterAttributes[] = ['attribute' => 'email', $filterType => "$filterPrefix$email$filterPostfix"];
            }
            if ($firstname && $lastname) {
                $filterAttributes[] = [
                    'attribute' => 'firstname',
                    $filterType => "$filterPrefix$firstname$filterPostfix"
                ];
                $filterAttributes[] = [
                    'attribute' => 'lastname',
                    $filterType => "$filterPrefix$lastname$filterPostfix"
                ];
            }

            if ($range) {
                // Get Range
                $ranges = [];
                $question = new Question('<question>Range start Id</question> ');
                $ranges[0] = $questionHelper->ask($input, $output, $question);

                $question = new Question('<question>Range end Id</question> ');
                $ranges[1] = $questionHelper->ask($input, $output, $question);

                // Ensure ascending order
                sort($ranges);

                // Range delete, takes precedence over --all
                $filterAttributes[] = [
                    'entity_id',
                    [
                        'from' => $ranges[0],
                        'to'   => $ranges[1],
                    ]
                ];
            }

            $customerCollection = $this->getCustomerCollection();
            $customerCollection->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname')
                ->addAttributeToSelect('email');
            if (count($filterAttributes)) {
                $customerCollection->addFieldToFilter($filterAttributes);
            }

            if ($this->shouldRemove($questionHelper, $input, $output)) {
                $count = $this->batchDelete($customerCollection);
                $this->output->writeln('<info>Successfully deleted ' . $count . ' customer/s</info>');
            } else {
                $this->output->writeln('<error>Aborting delete</error>');
            }
        }
    }

    /**
     * @return bool
     */
    protected function shouldRemove($questionHelper, $input, $output)
    {
        $shouldRemove = $this->input->getOption('force');
        if (!$shouldRemove) {
            $question = new ConfirmationQuestion(
                '<question>Are you sure?</question> <comment>[n]</comment>: ',
                false
            );
            $shouldRemove = $questionHelper->ask(
                $input,
                $output,
                $question
            );
        }

        return $shouldRemove;
    }

    /**
     * @param int|string $id
     *
     * @return Customer
     * @throws RuntimeException
     */
    protected function getCustomerById($id)
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->getById($id);
        if (!$customer->getId()) {
            /** @var $parameterHelper ParameterHelper */
            $parameterHelper = $this->getHelper('parameter');
            $website = $parameterHelper->askWebsite($this->input, $this->output);
            $email = $parameterHelper->askEmail($this->input, $this->output);
            $customer = $this->customerRepository()
                ->get($email, $website->getId());
        }

        if (!$customer->getId()) {
            throw new RuntimeException('No customer found!');
        }

        return $customer;
    }

    /**
     * @param CustomerCollection $customerCollection
     *
     * @return int
     */
    protected function batchDelete(CustomerCollection $customerCollection): int
    {
        $count = 0;
        foreach ($customerCollection as $customerToDelete) {
            $customer = $this->customerRepository->getById($customerToDelete->getId());
            $isSecure = $this->registry->registry('isSecureArea');

            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', true);
            if ($this->customerRepository->delete($customer)) {
                $count++;
            }
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', $isSecure);
        }

        return $count;
    }
}
