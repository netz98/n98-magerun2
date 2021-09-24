<?php

namespace N98\Magento\Command\Developer;

class DevelopmentHelper
{
    private $di;

    public function __construct($di)
    {
        $this->di = $di;
    }

    public function debugProductBySku($sku, $storeId = 0)
    {
        return $this->getProductRepository()->get($sku, false, $storeId)->debug();
    }

    public function debugProductById($id, $storeId = 0)
    {
        return $this->getProductRepository()->getById($id, false, $storeId)->debug();
    }

    public function debugCategoryById($id, $storeId = 0)
    {
        return $this->getCategoryRepository()->get($id, $storeId)->debug();
    }

    public function debugCustomerById($id)
    {
        return $this->getCustomerRepository()->getById($id)->debug();
    }

    public function debugCustomerByEmail($email, $websiteId = 0)
    {
        return $this->getCustomerRepository()->get($email, $websiteId)->debug();
    }

    public function getProductRepository()
    {
        return $this->di->get('Magento\Catalog\Model\ProductRepository');
    }

    public function getCategoryRepository()
    {
        return $this->di->get('Magento\Catalog\Model\CategoryRepository');
    }

    public function getCustomerRepository()
    {
        return $this->di->get('Magento\Customer\Api\CustomerRepositoryInterface');
    }


}
