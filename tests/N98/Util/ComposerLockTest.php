<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

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
