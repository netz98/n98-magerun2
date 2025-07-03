<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

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
