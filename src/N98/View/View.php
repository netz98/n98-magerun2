<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\View;

/**
 * Interface View
 * @package N98\View
 */
interface View
{
    /**
     * @param string $key
     * @param mixed $value
     * @return View
     */
    public function assign($key, $value);

    /**
     * @return string
     */
    public function render();
}
