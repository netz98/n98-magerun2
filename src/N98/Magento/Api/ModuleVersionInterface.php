<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Api;

interface ModuleVersionInterface
{
    /**
     * @return string
     */
    public function getDataVersion();

    /**
     * @return string
     */
    public function getDbVersion();

    /**
     * @param string $version
     * @return void
     */
    public function setDataVersion($version);

    /**
     * @param string $version
     * @return void
     */
    public function setDbVersion($version);
}
