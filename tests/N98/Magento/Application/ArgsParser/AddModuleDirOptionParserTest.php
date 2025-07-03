<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\ArgsParser;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Unit test for AddModuleDirOptionParser.
 */
class AddModuleDirOptionParserTest extends TestCase
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * Set up a temporary directory for testing real path resolution.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'n98-magerun-test-' . uniqid();
        if (!mkdir($this->tempDir, 0777, true) && !is_dir($this->tempDir)) {
            $this->fail("Could not create temporary directory: {$this->tempDir}");
        }
    }

    /**
     * Clean up the temporary directory.
     */
    protected function tearDown(): void
    {
        // To be safe, remove files inside the directory before removing the directory itself.
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    public function testParseWithNoArguments(): void
    {
        $parser = new AddModuleDirOptionParser([]);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getParameterOption')->willReturn(null);
        $outputMock = $this->createMock(OutputInterface::class);
        $this->assertEmpty($parser->parse($inputMock, $outputMock), 'Should return an empty array when no arguments are provided.');
    }

    public function testParseWithEqualsSyntax(): void
    {
        $parser = new AddModuleDirOptionParser([]);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getParameterOption')
            ->with('--add-module-dir', null, true)
            ->willReturn('/var/www/html/app/code/local');
        $outputMock = $this->createMock(OutputInterface::class);
        $this->assertEquals(
            ['/var/www/html/app/code/local'],
            $parser->parse($inputMock, $outputMock),
            'Should correctly parse --add-module-dir with "=" syntax.'
        );
    }

    public function testParseWithSpaceSyntax(): void
    {
        $parser = new AddModuleDirOptionParser([]);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getParameterOption')
            ->with('--add-module-dir', null, true)
            ->willReturn('/var/www/html/app/code/community');
        $outputMock = $this->createMock(OutputInterface::class);
        $this->assertEquals(
            ['/var/www/html/app/code/community'],
            $parser->parse($inputMock, $outputMock),
            'Should correctly parse --add-module-dir with space syntax.'
        );
    }

    public function testParseWithMultipleDirectories(): void
    {
        $parser = new AddModuleDirOptionParser([]);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getParameterOption')
            ->with('--add-module-dir', null, true)
            ->willReturn(['/path/one', '/path/two']);
        $outputMock = $this->createMock(OutputInterface::class);
        $this->assertEquals(
            ['/path/one', '/path/two'],
            $parser->parse($inputMock, $outputMock),
            'Should parse multiple directories correctly.'
        );
    }

    public function testParseSkipsEmptyPaths(): void
    {
        $parser = new AddModuleDirOptionParser([]);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getParameterOption')
            ->with('--add-module-dir', null, true)
            ->willReturn(['', '/valid/path', '', '/another/valid']);
        $outputMock = $this->createMock(OutputInterface::class);
        $this->assertEquals(
            ['/valid/path', '/another/valid'],
            $parser->parse($inputMock, $outputMock),
            'Should skip empty paths and return only valid ones.'
        );
    }

    public function testParseWithNoValidPaths(): void
    {
        $parser = new AddModuleDirOptionParser([]);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getParameterOption')
            ->with('--add-module-dir', null, true)
            ->willReturn(['', '']);
        $outputMock = $this->createMock(OutputInterface::class);
        $this->assertEmpty($parser->parse($inputMock, $outputMock), 'Should return an empty array if all paths are empty.');
    }
}
