<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

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
