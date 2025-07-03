<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <mot@fsfe.org>
 */

namespace N98\Util;

/**
 * Class AutoloadRestorerTest
 *
 * @package N98\Util
 */
class AutoloadRestorerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $restorer = new AutoloadRestorer();

        $this->assertInstanceOf('N98\Util\AutoloadRestorer', $restorer);
    }

    /**
     * @test
     */
    public function restoration()
    {
        $callbackStub = function () {
        };

        $this->assertTrue(spl_autoload_register($callbackStub));

        $restorer = new AutoloadRestorer();

        $this->assertTrue(in_array($callbackStub, spl_autoload_functions(), true));

        $this->assertTrue(spl_autoload_unregister($callbackStub));

        $this->assertFalse(in_array($callbackStub, spl_autoload_functions(), true));

        $restorer->restore();

        $this->assertTrue(in_array($callbackStub, spl_autoload_functions(), true));
    }
}
