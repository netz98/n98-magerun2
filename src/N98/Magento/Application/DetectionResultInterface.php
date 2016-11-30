<?php
/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application;

interface DetectionResultInterface
{
    /**
     * @return string
     */
    public function getRootFolder();

    /**
     * @return bool
     */
    public function isEnterpriseEdition();

    /**
     * @return int
     */
    public function getMajorVersion();

    /**
     * @return boolean
     */
    public function isMagerunStopFileFound();

    /**
     * @return string
     */
    public function getMagerunStopFileFolder();
}
