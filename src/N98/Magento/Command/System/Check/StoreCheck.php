<?php

namespace N98\Magento\Command\System\Check;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Interface StoreCheck
 *
 * @package N98\Magento\Command\System\Check
 */
interface StoreCheck
{
    /**
     * @param ResultCollection       $results
     * @param StoreInterface $store
     *
     * @return void
     */
    public function check(ResultCollection $results, StoreInterface $store);
}
