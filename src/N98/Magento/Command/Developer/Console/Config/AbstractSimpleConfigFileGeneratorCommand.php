<?php

namespace N98\Magento\Command\Developer\Console\Config;

use Magento\Framework\App\AreaList;
use N98\Magento\Command\Developer\Console\AbstractGeneratorCommand;

/**
 * Class AbstractSimpleConfigFileGeneratorCommand
 * @package N98\Magento\Command\Developer\Console\Config
 */
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
