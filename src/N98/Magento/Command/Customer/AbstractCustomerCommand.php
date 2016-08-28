<?php

namespace N98\Magento\Command\Customer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Resource\Customer\Collection as CustomerCollection;
use N98\Magento\Command\AbstractMagentoCommand;

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
}
