<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Framework;

/**
 * Interface AreaAware
 * @package N98\Magento\Framework
 */
interface AreaAware
{
    /**
     * Area to initialize
     * @return string
     */
    public function getAreaCode();
}
