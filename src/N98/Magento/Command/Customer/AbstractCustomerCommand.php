<?php

namespace N98\Magento\Command\Customer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Resource\Customer\Collection as CustomerCollection;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractCustomerCommand extends AbstractMagentoCommand
{
    /**
     * @return array
     */
    protected function getCustomerList($search = null)
    {
        $customerCollection = $this->getCustomerCollection();

        // Filter
        if ($search !== null) {
            $filter = [
                ['attribute' => 'email', 'like' => '%' . $search . '%'],
                ['attribute' => 'firstname', 'like' => '%' . $search . '%'],
                ['attribute' => 'lastname', 'like' => '%' . $search . '%'],
            ];
            $customerCollection->addAttributeToFilter(
                $filter
            );
        }

        // Result
        $list = [];
        foreach ($customerCollection as $customer) {
            /* @var $customer Customer */

            $list[] = [
                'id'         => $customer->getId(),
                'firstname'  => $customer->getFirstname(),
                'lastname'   => $customer->getLastname(),
                'email'      => $customer->getEmail(),
                'website'    => $customer->getWebsiteId(),
                'created_at' => $customer->getCreatedAt(),
            ];
        }

        return $list;
    }

    /**
     * @return Customer
     */
    protected function getCustomer()
    {
        return $this->getObjectManager()->create(Customer::class);
    }

    /**
     * @return CustomerCollection
     */
    protected function getCustomerCollection()
    {
        return $this->getCustomer()->getCollection();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return Customer|void
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
