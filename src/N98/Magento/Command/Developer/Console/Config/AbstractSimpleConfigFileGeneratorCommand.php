<?php
/**
 * netz98 magento module
 *
 * LICENSE
 *
 * This source file is subject of netz98.
 * You may be not allowed to change the sources
 * without authorization of netz98 new media GmbH.
 *
 * @copyright  Copyright (c) 1999-2016 netz98 new media GmbH (http://www.netz98.de)
 * @author netz98 new media GmbH <info@netz98.de>
 * @category N98
 * @package N98\Magento\Command\Developer\Console
 */

namespace N98\Magento\Command\Developer\Console\Config;

use Magento\Framework\App\AreaList;
use N98\Magento\Command\Developer\Console\AbstractGeneratorCommand;

abstract class AbstractSimpleConfigFileGeneratorCommand extends AbstractGeneratorCommand
{
    /**
     * @param string $configFileName
     * @param string $area
     * @return string
     */
    protected function getRelativeConfigFilePath($configFileName, $area = 'global')
    {
        if ($area == 'global') {
            $relativeConfigFilePath = 'etc/' . $configFileName;
        } else {
            $this->validateArea($area);
            $relativeConfigFilePath = 'etc/' . $area . '/' . $configFileName;
        }

        return $relativeConfigFilePath;
    }

    /**
     * @param string $selectedArea
     */
    private function validateArea($selectedArea)
    {
        /** @var $areaList AreaList */
        $areaList = $this->get(AreaList::class);
        $areaCodes = $areaList->getCodes();

        if (!in_array($selectedArea, $areaCodes)) {
            throw new \InvalidArgumentException('Invalid area. Available ares are (' . implode(',', $areaCodes) . ')');
        }
    }
}