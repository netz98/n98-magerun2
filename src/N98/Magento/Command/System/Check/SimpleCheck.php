<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check;

/**
 * Interface SimpleCheck
 *
 * @package N98\Magento\Command\System\Check
 */
interface SimpleCheck
{
    /**
     * @param ResultCollection $results
     *
     * @return void
     */
    public function check(ResultCollection $results);
}
