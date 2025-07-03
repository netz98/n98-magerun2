<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\Registry;
use N98\Util\Console\Helper\ParameterHelper;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
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
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force delete')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Delete all customers. Ignore all filters.')
            ->addOption('range', 'r', InputOption::VALUE_NONE, 'Delete a range of customers by Id')
            ->addOption('fuzzy', null, InputOption::VALUE_NONE, 'Fuzziness')
            ->setDescription('Deletes a customer/user for shop frontend by given options by matching or fuzzy search and/or range.');
    }

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Registry                    $registry
     */
    public function inject(
        CustomerRepositoryInterface $customerRepository,
        Registry                    $registry
    ) {
        $this->customerRepository = $customerRepository;
        $this->registry = $registry;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');

        // defaults
        $filterType = 'eq';
        $filterPrefix = '';
        $filterPostfix = '';
        $filterAttributes = [];

        // arguments
        $id = $input->getOption('id');
        $email = $input->getOption('email');
        $firstname = $input->getOption('firstname');
        $lastname = $input->getOption('lastname');
        $website = $input->getOption('website');

        // options
        $range = $input->getOption('range');
        $all = $input->getOption('all');
        $fuzzy = $input->getOption('fuzzy');
        $force = $input->getOption('force');

        // do not combine all with filter range, fuzzy, id, email, firstname, lastname
        if ($all && ($range || $fuzzy || $id || $email || $firstname || $lastname || $website)) {
            $output->writeln('<error>Combining --all with other options is not allowed</error>');
            return Command::FAILURE;
        }

        if ($all) {
            return $this->deleteAllCustomers($force, $questionHelper, $input, $output);
        }

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
        if (!($id || ($website && ($email || ($lastname && $firstname)))) && ($range || $all || $fuzzy)) {
            // Delete more than one customer ?
            $question = new Question('<question>Delete more than 1 customer?</question> <comment>[n]</comment>: ');
            $batchDelete = $questionHelper->ask($input, $output, $question);

            if ($batchDelete) {
                $range = $input->getOption('range');
                if ($all === null) {
                    $question = new ConfirmationQuestion(
                        '<question>Delete a range of customers?</question> <comment>[n]</comment>: ',
                        false
                    );
                    $range = $questionHelper->ask($input, $output, $question);
                }

                if (!$range) {
                    // Nothing to do
                    $output->writeln('<error>Finished nothing to do</error>');
                    return false;
                }
            }
        }

        if ($id) {
            // Single customer deletion without fuzziness
            // get customer by one of 'id' or 'email' or 'firstname' and 'lastname'
            try {
                $customer = $this->getCustomerById($input, $output, $id);
            } catch (Exception $e) {
                $output->writeln('<error>No customer found!</error>');
                return false;
            }

            if ($force || $this->shouldRemove($questionHelper, $input, $output)) {
                $isSecure = $this->registry->registry('isSecureArea');
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', true);
                $this->customerRepository->delete($customer);
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', $isSecure);
            } else {
                $output->writeln('<error>Aborting delete</error>');
            }
        } else {
            if ($fuzzy) {
                $filterType = 'like';
                $filterPrefix = '%';
                $filterPostfix = '%';
            }

            if ($website !== null && strlen($website) > 0) {
                $parameterHelper = $this->getHelper('parameter');
                $website = $parameterHelper->askWebsite($input, $output);
                $filterAttributes[] = ['attribute' => 'website_id', $filterType => $website->getId()];
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
                    'attribute' => 'entity_id',
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

            if (count($filterAttributes) === 0) {
                $output->writeln(
                    '<warning>No filter was specified. To delete all customer, ' .
                             'add the --all option</warning>'
                );

                return Command::FAILURE;
            }

            $customerCollection->addFieldToFilter($filterAttributes);

            $output->writeln(
                sprintf(
                    '<info>Command will delete <comment>%s</comment> customers.</info>',
                    $customerCollection->getSize()
                )
            );

            if ($force || $this->shouldRemove($questionHelper, $input, $output)) {
                $count = $this->batchDelete($customerCollection);
                $output->writeln('<info>Successfully deleted ' . $count . ' customer/s</info>');
            } else {
                $output->writeln('<error>Aborting delete</error>');
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @return bool
     */
    protected function shouldRemove($questionHelper, $input, $output)
    {
        $question = new ConfirmationQuestion(
            '<question>Are you sure?</question> <comment>[n]</comment>: ',
            false
        );
        return $questionHelper->ask(
            $input,
            $output,
            $question
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int|string $id
     *
     * @return Customer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCustomerById(InputInterface $input, OutputInterface $output, $id)
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->getById($id);
        if (!$customer->getId()) {
            /** @var $parameterHelper ParameterHelper */
            $parameterHelper = $this->getHelper('parameter');
            $website = $parameterHelper->askWebsite($input, $output);
            $email = $parameterHelper->askEmail($input, $output);
            $customer = $this->customerRepository->get($email, $website->getId());
        }

        if (!$customer->getId()) {
            throw new RuntimeException('No customer found!');
        }

        return $customer;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function batchDelete(Collection $customerCollection): int
    {
        $count = 0;
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $isSecure = $this->registry->registry('isSecureArea');
        foreach ($customerCollection as $customerToDelete) {
            $customer = $this->customerRepository->getById($customerToDelete->getId());
            if ($this->customerRepository->delete($customer)) {
                $count++;
            }
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', $isSecure);

        return $count;
    }

    /**
     * @param $force
     * @param QuestionHelper $questionHelper
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function deleteAllCustomers(
        $force,
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output
    ): int {
        if (!$force) {
            $question = new ConfirmationQuestion(
                '<question>WARNING: You are about to delete ALL customers. Are you sure?</question> <comment>[n]</comment>: ',
                false
            );
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Operation cancelled.</error>');
                return Command::FAILURE;
            }
        }

        // Proceed with deletion of all customers
        $customerCollection = $this->getCustomerCollection();
        $count = $this->batchDelete($customerCollection);
        $output->writeln('<info>Successfully deleted ' . $count . ' customer/s</info>');

        return Command::SUCCESS;
    }
}
