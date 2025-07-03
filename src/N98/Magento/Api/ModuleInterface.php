<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Api;

/**
 * Magento Module
 *
 * @package N98\Magento\Api
 */
interface ModuleInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getVersion();
}
