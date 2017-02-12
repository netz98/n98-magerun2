<?php
/**
 * Created by PhpStorm.
 * User: mot
 * Date: 12.02.17
 * Time: 12:09
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
     */
    public function setDataVersion($version);

    /**
     * @param string $version
     */
    public function setDbVersion($version);
}
