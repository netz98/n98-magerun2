<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class FlushCommandTest extends TestCase
{
    private string $root;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/magerun-flush-' . bin2hex(random_bytes(8));
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->root);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->root);
    }

    private function file(string $path): string
    {
        $path = $this->root . '/' . $path;
        $this->filesystem->mkdir(dirname($path));
        file_put_contents($path, 'keep or remove');

        return $path;
    }

    private function runFlush(bool $force = false): CommandTester
    {
        $command = new class($this->root) extends FlushCommand {
            public function __construct(string $root)
            {
                $this->_magentoRootFolder = $root;
                parent::__construct();
            }

            public function detectMagento(OutputInterface $output, $silent = true): bool
            {
                return true;
            }
        };
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($command);
        $tester->execute($force ? ['--force' => true] : []);

        return $tester;
    }

    public function testDefaultCleansAllSandboxVarDirectoriesOnly(): void
    {
        $base = 'dev/tests/integration/tmp/';
        $first = $this->file($base . 'sandbox-one/var/cache/.hidden');
        $second = $this->file($base . 'sandbox-two/var/log/test.log');
        $keep = [
            $this->file($base . 'sandbox-one/app/etc/config.php'),
            $this->file($base . 'sandbox-empty/app/etc/config.php'),
            $this->file($base . 'other/var/cache.txt'),
            $this->file($base . 'sandbox-/var/cache.txt'),
            $this->file('var/cache/keep'),
        ];
        $tester = $this->runFlush();
        self::assertSame(0, $tester->getStatusCode());
        self::assertFileDoesNotExist($first);
        self::assertFileDoesNotExist($second);
        self::assertDirectoryDoesNotExist($this->root . '/' . $base . 'sandbox-one/var');
        foreach ($keep as $path) {
            self::assertFileExists($path);
        }
        self::assertStringContainsString('Removed 2 integration test var directories.', $tester->getDisplay());
        self::assertSame(0, $this->runFlush()->getStatusCode());
    }

    public function testForceRemovesEntireSandboxesWithoutFollowingNestedLinks(): void
    {
        $keep = $this->file('outside/keep');
        $this->file('dev/tests/integration/tmp/sandbox-one/app/config');
        $this->file('dev/tests/integration/tmp/sandbox-two/app/config');
        $this->filesystem->symlink($this->root . '/outside', $this->root . '/dev/tests/integration/tmp/sandbox-one/var');
        $tester = $this->runFlush(true);
        self::assertSame(0, $tester->getStatusCode());
        self::assertDirectoryDoesNotExist($this->root . '/dev/tests/integration/tmp/sandbox-one');
        self::assertDirectoryDoesNotExist($this->root . '/dev/tests/integration/tmp/sandbox-two');
        self::assertFileExists($keep);
    }

    public function testNestedLinksAreNotFollowedByDefault(): void
    {
        $keep = $this->file('outside/keep');
        $this->filesystem->mkdir($this->root . '/dev/tests/integration/tmp/sandbox-one/var');
        $this->filesystem->symlink($this->root . '/outside', $this->root . '/dev/tests/integration/tmp/sandbox-one/var/link');
        self::assertSame(0, $this->runFlush()->getStatusCode());
        self::assertFileExists($keep);
    }

    /** @dataProvider linkedBoundaries */
    public function testLinkedBoundariesAreRefused(string $boundary, bool $force): void
    {
        $keep = $this->file('outside/keep');
        $link = $this->root . '/' . $boundary;
        $this->filesystem->mkdir(dirname($link));
        $this->filesystem->symlink($this->root . '/outside', $link);
        $tester = $this->runFlush($force);
        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Refusing symbolic link', $tester->getDisplay());
        self::assertTrue(is_link($link));
        self::assertFileExists($keep);
    }

    public static function linkedBoundaries(): array
    {
        return [
            ['dev', false],
            ['dev/tests', true],
            ['dev/tests/integration', false],
            ['dev/tests/integration/tmp', true],
            ['dev/tests/integration/tmp/sandbox-linked', false],
            ['dev/tests/integration/tmp/sandbox-linked', true],
            ['dev/tests/integration/tmp/sandbox-one/var', false],
        ];
    }

    public function testMissingTreeAndNoSandboxesAreSuccessful(): void
    {
        self::assertSame(0, $this->runFlush()->getStatusCode());
        $this->file('dev/tests/integration/tmp/unrelated');
        $this->file('dev/tests/integration/tmp/sandbox-file');
        self::assertSame(0, $this->runFlush(true)->getStatusCode());
    }

    public function testDanglingSandboxLinkIsRefused(): void
    {
        $tmp = $this->root . '/dev/tests/integration/tmp';
        $this->filesystem->mkdir($tmp);
        symlink($this->root . '/missing', $tmp . '/sandbox-broken');
        self::assertSame(1, $this->runFlush(true)->getStatusCode());
        self::assertTrue(is_link($tmp . '/sandbox-broken'));
    }

    public function testInvalidVarReturnsFailureButOtherSandboxesAreCleaned(): void
    {
        $invalid = $this->file('dev/tests/integration/tmp/sandbox-invalid/var');
        $valid = $this->file('dev/tests/integration/tmp/sandbox-valid/var/cache/file');
        $tester = $this->runFlush();
        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Expected a directory', $tester->getDisplay());
        self::assertFileExists($invalid);
        self::assertFileDoesNotExist($valid);
    }

    public function testInvalidTmpReturnsFailure(): void
    {
        $this->file('dev/tests/integration/tmp');
        self::assertSame(1, $this->runFlush()->getStatusCode());
    }

    public function testDeletionFailureReturnsFailure(): void
    {
        $this->file('dev/tests/integration/tmp/sandbox-one/var/file');
        $sandbox = $this->root . '/dev/tests/integration/tmp/sandbox-one';
        chmod($sandbox, 0555);
        try {
            if (is_writable($sandbox)) {
                self::markTestSkipped('Requires an unprivileged user to simulate a deletion failure.');
            }
            self::assertSame(1, $this->runFlush()->getStatusCode());
        } finally {
            chmod($sandbox, 0755);
        }
    }
}
