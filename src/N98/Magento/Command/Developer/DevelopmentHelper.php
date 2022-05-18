<?php

namespace N98\Magento\Command\Developer;

class DevelopmentHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $di;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $di
     */
    public function __construct($di)
    {
        $this->di = $di;
    }

    /**
     * @param string $sku
     * @param int $storeId
     */
    public function debugProductBySku($sku, $storeId = 0)
    {
        return $this->getProductRepository()->get($sku, false, $storeId)->debug();
    }

    /**
     * @return \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public function getProductRepository()
    {
        return $this->di->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @param int $id
     * @param int $storeId
     * @return mixed
     */
    public function debugProductById($id, $storeId = 0)
    {
        return $this->getProductRepository()->getById($id, false, $storeId)->debug();
    }

    public function debugCategoryById($id, $storeId = 0)
    {
        return $this->getCategoryRepository()->get($id, $storeId)->debug();
    }

    /**
     * @return \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    public function getCategoryRepository()
    {
        return $this->di->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function debugOrderById($id)
    {
        return $this->getOrderRepository()->get($id)->debug();
    }

    /**
     * @return \Magento\Sales\Api\OrderRepositoryInterface
     */
    public function getOrderRepository()
    {
        return $this->di->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function debugCustomerById($id)
    {
        return $this->getCustomerModel()->load($id)->debug();
    }

    /**
     * @param $email
     * @return array
     */
    public function debugCustomerByEmail($email, $websiteId = 0)
    {
        // Remark: For simplicity website is not validated and only has an effect, if
        // the configuration customer/account_share/scope is 1
        return $this->getCustomerModel()->setWebsiteId($websiteId)->loadByEmail($email)->debug();
    }

    /**
     * @return \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public function getCustomerRepository()
    {
        return $this->di->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerModel()
    {
        return $this->di->get(\Magento\Customer\Model\Customer::class);
    }

    /**
     * @param int $cartId
     * @return mixed
     */
    public function debugCartById($cartId)
    {
        return $this->getCartRepository()->get($cartId)->debug();
    }

    /**
     * @return \Magento\Quote\Api\CartRepositoryInterface
     */
    public function getCartRepository()
    {
        return $this->di->get(\Magento\Quote\Api\CartRepositoryInterface::class);
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->di->get(\Magento\Store\Model\StoreManagerInterface::class);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function createProductModel()
    {
        return $this->di->get(\Magento\Catalog\Model\ProductFactory::class)->create();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function createCustomerModel()
    {
        return $this->di->get(\Magento\Customer\Model\CustomerFactory::class)->create();
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->di->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
    }

    /**
     * @return \Magento\Eav\Api\AttributeRepositoryInterface
     */
    public function getEavAttributeRepository()
    {
        return $this->di->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);
    }

    /**
     * @return \Magento\Cms\Api\BlockRepositoryInterface
     */
    public function getCmsBlockRepository()
    {
        return $this->di->get(\Magento\Cms\Api\BlockRepositoryInterface::class);
    }

    /**
     * @return \Magento\Cms\Api\PageRepositoryInterface
     */
    public function getCmsPageRepository()
    {
        return $this->di->get(\Magento\Cms\Api\PageRepositoryInterface::class);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    public function getDatabaseConnection()
    {
        return $this->di->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
    }
}
