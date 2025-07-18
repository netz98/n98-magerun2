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

use PHPUnit\Framework\TestCase;

/**
 * Class FilesystemTest
 * @package N98\Util
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @covers N98\Util\Filesystem
 */
class FilesystemTest extends TestCase
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = new Filesystem();
    }

    public function testRecursiveCopy()
    {
        $this->expectException(\RuntimeException::class);
        $tmp = sys_get_temp_dir();
        $basePath = $tmp . "/n98_testdir";
        $folder1 = $basePath . "/folder1";
        $folder2 = $basePath . "/folder2";
        $file1 = $folder1 . "/file1.txt";
        $file2 = $folder2 . "/file2.txt";
        $dest = sys_get_temp_dir() . "/n98_copy_dest";

        @mkdir($folder1, 0777, true);
        @mkdir($folder2, 0777, true);
        touch($file1);
        touch($file2);

        $this->fileSystem->recursiveCopy($basePath, $dest);
        $this->assertFileExists($dest . "/folder1/file1.txt");
        $this->assertFileExists($dest . "/folder2/file2.txt");

        //cleanup
        unlink($file1);
        unlink($file2);
        rmdir($folder1);
        rmdir($folder2);
        rmdir($basePath);

        unlink($dest . "/folder1/file1.txt");
        unlink($dest . "/folder2/file2.txt");
        rmdir($dest . "/folder1");
        rmdir($dest . "/folder2");
        rmdir($dest);

        $this->assertFileDoesNotExist($dest . "/folder1/file1.txt");
        $this->assertFileDoesNotExist($dest);

        is_dir($tmp . '/a') || mkdir($tmp . '/a');
        touch($tmp . '/file1.txt');
        $this->fileSystem->recursiveCopy($tmp . '/a', $tmp . '/file1.txt');
        unlink($tmp . '/file1.txt');
        rmdir($tmp . '/a');
    }

    public function testRecursiveCopyWithBlacklist()
    {
        $tmp = sys_get_temp_dir();
        $basePath = $tmp . "/n98_testdir";
        $folder1 = $basePath . "/folder1";
        $folder2 = $basePath . "/folder2";
        $file1 = $folder1 . "/file1.txt";
        $ignoreMe = $folder1 . "/ignore.me";
        $file2 = $folder2 . "/file2.txt";
        $dest = sys_get_temp_dir() . "/n98_copy_dest";

        @mkdir($folder1, 0777, true);
        @mkdir($folder2, 0777, true);
        touch($file1);
        touch($file2);

        $this->fileSystem->recursiveCopy($basePath, $dest, ['ignore.me']);
        $this->assertFileExists($dest . "/folder1/file1.txt");
        $this->assertFileExists($dest . "/folder2/file2.txt");
        $this->assertFileDoesNotExist($dest . "/folder1/ignore.me");

        //cleanup
        unlink($file1);
        unlink($file2);
        rmdir($folder1);
        rmdir($folder2);
        rmdir($basePath);

        unlink($dest . "/folder1/file1.txt");
        unlink($dest . "/folder2/file2.txt");
        rmdir($dest . "/folder1");
        rmdir($dest . "/folder2");
        rmdir($dest);
    }

    /**
     * @requires function symlink
     */
    public function testRecursiveDirectoryRemoveUnLinksSymLinks()
    {
        $tmp = sys_get_temp_dir();
        $basePath = $tmp . "/n98_testdir";
        $symLinked = $tmp . "/n98_linked";
        $symLinkedFile = $symLinked . "/symlinkme.txt";

        @mkdir($basePath, 0777, true);
        @mkdir($symLinked, 0777, true);

        touch($symLinkedFile);

        $result = @symlink($symLinked, $basePath . "/symlink");
        $this->assertTrue($result);

        $this->fileSystem->recursiveRemoveDirectory($basePath);

        $this->assertFileExists($symLinkedFile);
        $this->assertFileDoesNotExist($basePath);
    }

    public function testRecursiveRemove()
    {
        $tmp = sys_get_temp_dir();
        $basePath = $tmp . "/n98_testdir";
        $folder1 = $basePath . "/folder1";
        $folder2 = $basePath . "/folder2";
        $file1 = $folder1 . "/file1.txt";
        $file2 = $folder2 . "/file2.txt";

        @mkdir($folder1, 0777, true);
        @mkdir($folder2, 0777, true);
        touch($file1);
        touch($file2);

        $this->fileSystem->recursiveRemoveDirectory($basePath);
        $this->assertFileDoesNotExist($basePath);
    }

    public function testRecursiveRemoveWithTrailingSlash()
    {
        $tmp = sys_get_temp_dir();
        $basePath = $tmp . "/n98_testdir";
        $folder1 = $basePath . "/folder1";
        $folder2 = $basePath . "/folder2";
        $file1 = $folder1 . "/file1.txt";
        $file2 = $folder2 . "/file2.txt";

        @mkdir($folder1, 0777, true);
        @mkdir($folder2, 0777, true);
        touch($file1);
        touch($file2);

        $this->fileSystem->recursiveRemoveDirectory($basePath . "/");
        $this->assertFileDoesNotExist($basePath);
    }

    public function testFalseIsReturnedIfDirectoryNotExist()
    {
        $this->assertFalse($this->fileSystem->recursiveRemoveDirectory("not-a-folder"));
    }

    public function testFalseIsReturnedIfDirectoryNotReadable()
    {
        $tmp = sys_get_temp_dir();
        $basePath = $tmp . "/n98_testdir-never-existed";

        $this->assertFalse($this->fileSystem->recursiveRemoveDirectory($basePath));
    }

    public function testParentIsNotRemovedIfEmptyIsTrue()
    {
        $tmp = sys_get_temp_dir();
        $basePath = $tmp . "/n98_testdir";
        $folder1 = $basePath . "/folder1";
        $folder2 = $basePath . "/folder2";
        $file1 = $folder1 . "/file1.txt";
        $file2 = $folder2 . "/file2.txt";

        @mkdir($folder1, 0777, true);
        @mkdir($folder2, 0777, true);
        touch($file1);
        touch($file2);

        $this->fileSystem->recursiveRemoveDirectory($basePath, true);
        $this->assertFileExists($basePath);
        $this->assertFileDoesNotExist($folder1);
        $this->assertFileDoesNotExist($folder2);
    }

    /**
     * @param int $bytes
     * @param int $decimalPlaces
     * @param string $expected
     * @dataProvider convertedBytesProvider
     */
    public function testConvertBytesToHumanReadable($bytes, $decimalPlaces, $expected)
    {
        $res = Filesystem::humanFileSize($bytes, $decimalPlaces);
        $this->assertSame($expected, $res);
    }

    /**
     * @return array
     */
    public static function convertedBytesProvider()
    {
        return [
            [20000000, 2, '19.07M'],
            [20000000, 3, '19.073M'],
            [2000000000, 2, '1.86G'],
            [2, 2, '2.00B'],
            [2048, 2, '2.00K'],
        ];
    }
}
