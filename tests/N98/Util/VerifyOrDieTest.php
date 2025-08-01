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

namespace N98\Util;

use PHPUnit\Framework\TestCase as TestCase;
use RuntimeException;

/**
 * Class VerifyOrDieTest
 *
 * @package N98\Util
 */
class VerifyOrDieTest extends TestCase
{
    /**
     * @test a portable filename passes
     */
    public function portableFilename()
    {
        $this->assertSame("example.txt", VerifyOrDie::filename("example.txt"));

        $this->assertSame(".hidden", VerifyOrDie::filename(".hidden"));
    }

    /**
     * @test user-message for verification
     */
    public function userMessage()
    {
        $message = sprintf('Database name %s is not portable', var_export('-fail', true));
        try {
            VerifyOrDie::filename('-fail', $message);
            $this->fail('An expected exception has not been thrown.');
        } catch (RuntimeException $e) {
            $this->assertSame($message, $e->getMessage());
        }
    }

    /**
     * @test a filename must have at least one byte
     */
    public function zeroLengthFilename()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Filename is zero-length string');
        VerifyOrDie::filename('');
    }

    /**
     * @test
     */
    public function invalidArugment()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter basename must be of type string, NULL given');
        VerifyOrDie::filename(null);
    }

    /**
     * @test a filename must not start with a dash
     */
    public function startWithDashFilename()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Filename '-rf' starts with a dash");
        VerifyOrDie::filename('-rf');
    }

    /**
     * @test
     * @dataProvider provideNonPortableFilenames
     */
    public function nonPortableFilenameThrowsException($filename)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not portable');
        VerifyOrDie::filename($filename);
    }

    /**
     * @see nonPortableFilenameThrowsException
     */
    public function provideNonPortableFilenames()
    {
        return [
            ['no-slash-/-in.there'],
            ['windoze-limits-<>:"/\\|?*'],
            ['lets-keep-spaces   out'],
        ];
    }
}
