<?php

declare(strict_types=1);

namespace N98\Util;

class ComposerLockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldBeIteratable()
    {
        $lock = new ComposerLock(__DIR__ . '/_files/sample-project/composer');
        $this->assertIsIterable($lock);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldReturnPackages()
    {
        $lock = new ComposerLock(__DIR__ . '/_files/sample-project/composer');
        $this->assertIsArray($lock->getPackages());

        $this->assertEquals('1.1.1', $lock->getPackageByName('psr/container')->version);
    }
}
