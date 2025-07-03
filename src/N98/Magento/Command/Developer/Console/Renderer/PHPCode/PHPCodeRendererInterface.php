<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console\Renderer\PHPCode;

/**
 * Interface PHPCodeRendererInterface
 * @package N98\Magento\Command\Developer\Console\Renderer\PHPCode
 */
interface PHPCodeRendererInterface
{
    /**
     * @return string
     */
    public function render();
}
