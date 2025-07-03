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

use N98\Util\Console\Helper\MagentoHelper;

/**
 * Class DetectionResult
 *
 * @package N98\Magento\Application
 */
class DetectionResult implements DetectionResultInterface
{
    /**
     * @var bool
     */
    private $detected;

    /**
     * @var MagentoHelper
     */
    private $helper;

    /**
     * DetectionResult constructor.
     *
     * @param MagentoHelper $helper
     * @param string $folder
     * @param array $subFolders
     */
    public function __construct(MagentoHelper $helper, $folder, array $subFolders = [])
    {
        $this->helper = $helper;
        $this->detected = $helper->detect($folder, $subFolders); // @TODO Constructor should not run "detect" method
    }

    /**
     * @return bool
     */
    public function isDetected()
    {
        return $this->detected;
    }

    /**
     * @return string
     */
    public function getRootFolder()
    {
        return $this->helper->getRootFolder();
    }

    /**
     * @return bool
     */
    public function isEnterpriseEdition()
    {
        return $this->helper->isEnterpriseEdition();
    }

    /**
     * @return int
     */
    public function getMajorVersion()
    {
        return $this->helper->getMajorVersion();
    }

    /**
     * @return boolean
     */
    public function isMagerunStopFileFound()
    {
        return $this->helper->isMagerunStopFileFound();
    }

    /**
     * @return string
     */
    public function getMagerunStopFileFolder()
    {
        return $this->helper->getMagerunStopFileFolder();
    }
}
