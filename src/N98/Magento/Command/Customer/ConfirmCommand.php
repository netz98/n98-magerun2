<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

class ConfirmCommand extends AbstractCustomerCommand
{
    private AccountManagementInterface $accountManagement;
    private CustomerRepositoryInterface $customerRepository;
    private StoreManagerInterface $storeManager;
    private State $state;
    private Emulation $emulation;

    protected function configure()
    {
        $this->setName('customer:confirm')
            ->addArgument('email', InputArgument::OPTIONAL, 'Customer email')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website code or ID')
            ->addOption('resend', null, InputOption::VALUE_NONE, 'Resend the confirmation email without confirming the account')
            ->setDescription('Confirms a pending customer account.')
            ->setHelp('Select a website when more than one exists. Already-confirmed accounts are never unconfirmed.');
    }

    public function inject(
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        State $state,
        Emulation $emulation
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->emulation = $emulation;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        if (!$input->isInteractive()) {
            if (!$email) {
                $output->writeln('<error>An email is required in non-interactive mode.</error>');
                return Command::FAILURE;
            }
            $errors = Validation::createValidator()->validate($email, [new NotBlank(), new Email()]);
            if (count($errors) > 0) {
                $output->writeln('<error>A valid email address is required in non-interactive mode.</error>');
                return Command::FAILURE;
            }
        }

        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        try {
            if ($input->isInteractive()) {
                $email = $this->getHelper('parameter')->askEmail($input, $output);
            }
            // Do not let askWebsite silently replace an explicitly invalid website.
            if ($input->getArgument('website') !== null) {
                $website = $this->storeManager->getWebsite($input->getArgument('website'));
            } else {
                if (!$input->isInteractive() && count($this->storeManager->getWebsites()) !== 1) {
                    $output->writeln('<error>A website is required in non-interactive mode when multiple websites exist.</error>');
                    return Command::FAILURE;
                }
                $website = $this->getHelper('parameter')->askWebsite($input, $output);
            }

            $customer = $this->customerRepository->get($email, $website->getId());
            if ((int) $customer->getWebsiteId() !== (int) $website->getId()) {
                $output->writeln('<error>Customer was not found in the selected website.</error>');
                return Command::FAILURE;
            }
            if (!$customer->getConfirmation()) {
                $output->writeln('<error>Customer account is already confirmed.</error>');
                return Command::FAILURE;
            }

            $store = $website->getDefaultStore();
            if (!$store) {
                $output->writeln('<error>The selected website has no default store.</error>');
                return Command::FAILURE;
            }

            $success = $this->state->emulateAreaCode(Area::AREA_FRONTEND, function () use ($input, $customer, $website, $store) {
                try {
                    $this->emulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);
                    if ($input->getOption('resend')) {
                        return $this->accountManagement->resendConfirmation($customer->getEmail(), $website->getId());
                    }
                    $this->accountManagement->activateById($customer->getId(), $customer->getConfirmation());
                    return true;
                } finally {
                    $this->emulation->stopEnvironmentEmulation();
                }
            });

            if (!$success) {
                $output->writeln('<error>Confirmation email could not be sent.</error>');
                return Command::FAILURE;
            }
            $output->writeln($input->getOption('resend')
                ? '<info>Confirmation email successfully sent. Account remains pending.</info>'
                : '<info>Customer account successfully confirmed.</info>');
            return Command::SUCCESS;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $output->writeln('<error>Customer or website was not found.</error>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . OutputFormatter::escape($e->getMessage()) . '</error>');
        }

        return Command::FAILURE;
    }
}
