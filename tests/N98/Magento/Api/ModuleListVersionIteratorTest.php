<?php
/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Api;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\ResourceInterface as ModuleResourceInterface;

/**
 * @covers \N98\Magento\Api\ModuleListVersionIterator
 */
class ModuleListVersionIteratorTest extends TestCase
{
    /**
     * @return ModuleListVersionIterator
     */
    private function getSubject()
    {
        /* @var $moduleList ModuleListInterface */
        $moduleList = $this->getObject(ModuleListInterface::class);

        /* @var $resource ModuleResourceInterface */
        $resource = $this->getObject(ModuleResourceInterface::class);

        $iterator = new ModuleListVersionIterator($moduleList, $resource);

        return $iterator;
    }

    /**
     * @test
     */
    public function creation()
    {
        $iterator = $this->getSubject();
        $this->assertInstanceOf(ModuleListVersionIterator::class, $iterator);
    }

    /**
     * @test
     */
    public function iteration()
    {
        $iterator = $this->getSubject();
        $array = iterator_to_array($iterator, false);
        $this->assertGreaterThan(0, count($array));
        $this->assertArrayHasKey(0, $array);
        $module = $array[0];
        $this->assertInstanceOf(ModuleVersionInterface::class, $module);
    }
}
