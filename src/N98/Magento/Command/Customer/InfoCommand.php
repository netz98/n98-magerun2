<?php

namespace N98\Magento\Command\Customer;

use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Customer\Model\Resource\Customer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends AbstractCustomerCommand
{
    /**
     * @var array
     */
    protected $blacklist = array(
        'password_hash',
        'increment_id',
    );

    protected function configure()
    {
        $this
            ->setName('customer:info')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website of the customer')
            ->setDescription('Loads basic customer info by email address.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Detect and Init Magento
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        // Detect customer
        $customer = $this->detectCustomer($input, $output);
        if ($customer === null) {
            return;
        }

        /** @var Customer $customerResource */
        $customerResource = $customer->getResource();

        // Prepare Table Data
        $table = [];
        foreach ($customer->toArray() as $key => $value) {
            if (in_array($key, $this->blacklist)) {
                continue;
            }
            try {
                $attribute = $customerResource->getAttribute($key);

                if (!$attribute instanceof CustomerAttribute) {
                    $table[] = [$key, $key, $value];
                    continue;
                }

                /** @var \Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend $attributeFrontend */
                $attributeFrontend = $attribute->getFrontend();

                $tableLabel = $attributeFrontend->getLabel();
                $tableValue = $value;

                // @todo mwr temporary work around due to getValue throwing notice within getOptionText
                // (array keys !== store_ids)
                if ($key != 'store_id') {
                    $tableValue = $attributeFrontend->getValue($customer);
                    if ($tableValue != $value) {
                        $tableValue .= " [$value]";
                    }
                }
                $table[] = [$key, $tableLabel, $tableValue];
            } catch (\Exception $e) {
                $table[] = [$key, $key, $value];
            }
        }

        // Render Table
        $helperTable = $this->getHelper('table');
        $helperTable->setHeaders(['Code', 'Name', 'Value']);
        $helperTable->setRows($table);
        $helperTable->render($output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return \Magento\Customer\Model\Customer|void
     */
    protected function detectCustomer(InputInterface $input, OutputInterface $output)
    {
        $helperParameter = $this->getHelper('parameter');
        $email = $helperParameter->askEmail($input, $output);
        $website = $helperParameter->askWebsite($input, $output);

        $customer = $this->getCustomer();
        $customer->setWebsiteId($website->getId());
        $customer->loadByEmail($email);
        $customerId = $customer->getId();
        if ($customerId <= 0) {
            $output->writeln('<error>Customer was not found</error>');

            return null;
        }

        $customer->load($customerId);

        return $customer;
    }
}
