<?php
/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Api;

use Magento\Framework\Module\ModuleListInterface;

/**
 * @covers \N98\Magento\Api\ModuleListIterator
 */
class ModuleListIteratorTest extends TestCase
{
    /**
     * @return ModuleListIterator
     */
    private function getSubject()
    {
        /* @var $moduleList ModuleListInterface */
        $moduleList = $this->getObject(ModuleListInterface::class);

        return new ModuleListIterator($moduleList);
    }

    /**
     * @test
     */
    public function creation()
    {
        $iterator = $this->getSubject();
        $this->assertInstanceOf(ModuleListIterator::class, $iterator);
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
        $this->assertInstanceOf(ModuleInterface::class, $module);
    }
}
