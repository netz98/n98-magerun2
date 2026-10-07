<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\FastDiCompile;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadBinaryCommandTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/n98-fast-di-compile-test-' . uniqid();
        mkdir($this->directory, 0700, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->directory);
    }

    public function testDownloadsAndVerifiesSelectedPlatformBinary(): void
    {
        $tester = $this->createTester('binary contents');

        $exitCode = $tester->execute([
            '--install-dir' => $this->directory,
            '--platform' => 'linux-arm64',
            '--release' => 'v1.2.3',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame('binary contents', file_get_contents($this->directory . '/fast-di-compile'));
        $this->assertTrue(is_executable($this->directory . '/fast-di-compile'));
        $this->assertStringContainsString('Installed fast-di-compile v1.2.3 for linux-arm64', $tester->getDisplay());
    }

    public function testRejectsUnsupportedPlatformBeforeDownloading(): void
    {
        $tester = $this->createTester('binary contents');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported platform: windows-x64');

        $tester->execute([
            '--install-dir' => $this->directory,
            '--platform' => 'windows-x64',
        ]);
    }

    public function testRefusesToReplaceDifferentExistingBinaryWithoutForce(): void
    {
        file_put_contents($this->directory . '/fast-di-compile', 'old binary');
        $tester = $this->createTester('new binary');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Existing binary does not match v1.2.3. Use --force.');

        $tester->execute([
            '--install-dir' => $this->directory,
            '--platform' => 'linux-x64',
        ]);
    }

    private function createTester(string $binaryContents): CommandTester
    {
        $binaryHash = hash('sha256', $binaryContents);
        $command = new class($binaryContents, $binaryHash) extends DownloadBinaryCommand {
            public function __construct(private string $binaryContents, private string $binaryHash)
            {
                parent::__construct();
            }

            protected function fetchRelease(?string $requestedVersion): array
            {
                return [
                    'version' => $requestedVersion ?? 'v1.2.3',
                    'checksums' => [
                        'fast-di-compile-v1.2.3-linux-x64.tar.gz' => hash('sha256', 'archive'),
                        'fast-di-compile-v1.2.3-linux-arm64.tar.gz' => hash('sha256', 'archive'),
                        'sha256sums.txt' => hash('sha256', $this->checksums()),
                    ],
                ];
            }

            protected function downloadFile(string $url, string $target, array $headers = []): void
            {
                file_put_contents($target, str_ends_with($url, 'sha256sums.txt') ? $this->checksums() : 'archive');
            }

            protected function extractBinary(string $archive, string $temporaryDirectory): string
            {
                $binary = $temporaryDirectory . '/fast-di-compile';
                file_put_contents($binary, $this->binaryContents);

                return $binary;
            }

            private function checksums(): string
            {
                return hash('sha256', 'archive') . "  fast-di-compile-v1.2.3-linux-x64.tar.gz\n"
                    . hash('sha256', 'archive') . "  fast-di-compile-v1.2.3-linux-arm64.tar.gz\n"
                    . $this->binaryHash . "  fast-di-compile\n";
            }
        };
        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('fast-di-compile:download-binary'));
    }
}
