<?php

namespace N98\Magento\Command\System\Check;

use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Interface WebsiteCheck
 *
 * @package N98\Magento\Command\System\Check
 */
interface WebsiteCheck
{
    /**
     * @param ResultCollection         $results
     * @param WebsiteInterface $website
     *
     * @return void
     */
    public function check(ResultCollection $results, WebsiteInterface $website);
}
