<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 *
 * Inspired by the fast-di-compile installer by Anton Siniorg (speedupmate).
 */

declare(strict_types=1);

namespace N98\Magento\Command\FastDiCompile;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Http\DownloadHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DownloadBinaryCommand extends AbstractMagentoCommand
{
    private const BINARY_NAME = 'fast-di-compile';
    private const GITHUB_API_URL = 'https://api.github.com/repos/speedupmate/di-compiler/releases/';
    private const GITHUB_DOWNLOAD_URL = 'https://github.com/speedupmate/di-compiler/releases/download/';
    private const MAX_DOWNLOAD_BYTES = 67108864;
    private const ALLOWED_DOWNLOAD_HOSTS = [
        'api.github.com',
        'github.com',
        'release-assets.githubusercontent.com',
        'objects.githubusercontent.com',
        'github-releases.githubusercontent.com',
    ];

    protected function configure(): void
    {
        $this
            ->setName('fast-di-compile:download-binary')
            ->setDescription('Download the Rust fast DI compiler binary by Anton Siniorg (speedupmate)')
            ->addOption('release', null, InputOption::VALUE_REQUIRED, 'Release tag to install, e.g. v1.0.3')
            ->addOption(
                'platform',
                null,
                InputOption::VALUE_REQUIRED,
                'Platform override: linux-x64, linux-arm64, macos-x64, or macos-arm64'
            )
            ->addOption('install-dir', null, InputOption::VALUE_REQUIRED, 'Directory to install into. Defaults to the bin directory of your Magento installation.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Replace an existing binary or symlink')
            ->setHelp(
                <<<'HELP'
Downloads a verified fast-di-compile release from speedupmate/di-compiler, authored by Anton Siniorg.

The command detects Linux/macOS and x64/arm64, downloads the matching release archive and its
SHA-256 checksum file, and verifies both before installing the executable at
<comment>bin/fast-di-compile</comment> in the Magento project.

Use <comment>--release</comment> to select a release, <comment>--platform</comment> to override detection, and
<comment>--force</comment> to replace an existing binary which does not match the selected release.
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $platform = $input->getOption('platform') !== null
            ? $this->validatePlatform((string) $input->getOption('platform'))
            : $this->detectPlatform();
        $version = $input->getOption('release') !== null
            ? $this->validateVersion((string) $input->getOption('release'))
            : null;
        $installDir = $input->getOption('install-dir') !== null
            ? (string) $input->getOption('install-dir')
            : $this->getApplication()->getMagentoRootFolder()
                . '/bin';

        $this->prepareInstallDirectory($installDir);
        $release = $this->fetchRelease($version);
        $assetName = self::BINARY_NAME . '-' . $release['version'] . '-' . $platform . '.tar.gz';
        $archiveHash = $this->requireChecksum($release['checksums'], $assetName);
        $checksumHash = $this->requireChecksum($release['checksums'], 'sha256sums.txt');
        $temporaryDirectory = $this->createTemporaryDirectory();

        try {
            $checksumsPath = $temporaryDirectory . '/sha256sums.txt';
            $this->downloadFile($this->releaseDownloadUrl($release['version'], 'sha256sums.txt'), $checksumsPath);
            $this->assertHash($checksumsPath, $checksumHash, 'sha256sums.txt');
            $checksums = $this->parseChecksums((string) file_get_contents($checksumsPath));
            if (!isset($checksums[$assetName]) || !hash_equals($archiveHash, $checksums[$assetName])) {
                throw new RuntimeException(sprintf('Checksum file does not agree with GitHub for %s.', $assetName));
            }

            $target = rtrim($installDir, '/') . '/' . self::BINARY_NAME;
            if (is_link($target) && !$input->getOption('force')) {
                throw new RuntimeException(sprintf('Refusing to replace symlink %s. Use --force.', $target));
            }

            $archivePath = $temporaryDirectory . '/' . $assetName;
            $this->downloadFile($this->releaseDownloadUrl($release['version'], $assetName), $archivePath);
            $this->assertHash($archivePath, $archiveHash, $assetName);
            $stagedBinary = $this->extractBinary($archivePath, $temporaryDirectory);

            if (isset($checksums[self::BINARY_NAME])) {
                $this->assertHash($stagedBinary, $checksums[self::BINARY_NAME], self::BINARY_NAME);
            }
            if (file_exists($target) && !$input->getOption('force')) {
                $stagedHash = hash_file('sha256', $stagedBinary);
                $targetHash = hash_file('sha256', $target);
                if (!is_string($stagedHash) || !is_string($targetHash) || !hash_equals($stagedHash, $targetHash)) {
                    throw new RuntimeException(sprintf('Existing binary does not match %s. Use --force.', $release['version']));
                }
            }
            if (!chmod($stagedBinary, 0755) || !rename($stagedBinary, $target)) {
                throw new RuntimeException(sprintf('Could not install binary to %s.', $target));
            }

            $output->writeln(sprintf('<info>Installed %s %s for %s</info>', self::BINARY_NAME, $release['version'], $platform));
            $output->writeln($target);

            return Command::SUCCESS;
        } finally {
            $this->removeDirectory($temporaryDirectory);
        }
    }

    protected function detectPlatform(): string
    {
        $os = match (PHP_OS_FAMILY) {
            'Linux' => 'linux',
            'Darwin' => 'macos',
            default => throw new RuntimeException('Unsupported operating system: ' . PHP_OS_FAMILY),
        };
        $architecture = match (strtolower(php_uname('m'))) {
            'x86_64', 'amd64' => 'x64',
            'aarch64', 'arm64' => 'arm64',
            default => throw new RuntimeException('Unsupported CPU architecture: ' . php_uname('m')),
        };

        return $os . '-' . $architecture;
    }

    /** @return array{version: string, checksums: array<string, string>} */
    protected function fetchRelease(?string $requestedVersion): array
    {
        $url = self::GITHUB_API_URL . ($requestedVersion === null ? 'latest' : 'tags/' . rawurlencode($requestedVersion));
        $temporaryFile = tempnam(sys_get_temp_dir(), 'n98-fast-di-release-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Could not create a temporary release file.');
        }
        try {
            $this->downloadFile($url, $temporaryFile, ['Accept: application/vnd.github+json']);
            $release = json_decode((string) file_get_contents($temporaryFile), true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('GitHub release response is not valid JSON.', 0, $exception);
        } finally {
            @unlink($temporaryFile);
        }
        if (!is_array($release) || ($release['draft'] ?? true) || ($requestedVersion === null && ($release['prerelease'] ?? true))) {
            throw new RuntimeException('GitHub did not return a stable release.');
        }
        $version = $this->validateVersion((string) ($release['tag_name'] ?? ''));
        $checksums = [];
        foreach ($release['assets'] ?? [] as $asset) {
            if (is_array($asset) && isset($asset['name'], $asset['digest'])
                && preg_match('/^sha256:([a-fA-F0-9]{64})$/', (string) $asset['digest'], $matches) === 1
            ) {
                $checksums[(string) $asset['name']] = strtolower($matches[1]);
            }
        }

        return ['version' => $version, 'checksums' => $checksums];
    }

    /** @param list<string> $headers */
    protected function downloadFile(string $url, string $target, array $headers = []): void
    {
        DownloadHelper::download($url, $target, self::ALLOWED_DOWNLOAD_HOSTS, self::MAX_DOWNLOAD_BYTES, $headers);
    }

    protected function extractBinary(string $archive, string $temporaryDirectory): string
    {
        $process = new Process(['tar', '-xzf', $archive, '-C', $temporaryDirectory, './' . self::BINARY_NAME]);
        $process->setTimeout(30);
        $process->run();
        $binary = $temporaryDirectory . '/' . self::BINARY_NAME;
        if (!$process->isSuccessful() || !is_file($binary) || is_link($binary)) {
            throw new RuntimeException('Could not extract fast-di-compile from release archive.');
        }

        return $binary;
    }

    /** @return array<string, string> */
    private function parseChecksums(string $contents): array
    {
        $checksums = [];
        foreach (preg_split('/\r?\n/', $contents) ?: [] as $line) {
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (preg_match('/^([a-fA-F0-9]{64})[ \t]+\*?([A-Za-z0-9._-]+)$/', $line, $matches) !== 1) {
                throw new RuntimeException('Checksum file contains an invalid line.');
            }
            if (isset($checksums[$matches[2]])) {
                throw new RuntimeException('Checksum file contains a duplicate entry.');
            }
            $checksums[$matches[2]] = strtolower($matches[1]);
        }

        return $checksums;
    }

    private function validatePlatform(string $platform): string
    {
        if (preg_match('/^(linux|macos)-(x64|arm64)$/', $platform) !== 1) {
            throw new RuntimeException('Unsupported platform: ' . $platform);
        }

        return $platform;
    }

    private function validateVersion(string $version): string
    {
        if (preg_match('/^v[0-9][0-9A-Za-z._-]*$/', $version) !== 1) {
            throw new RuntimeException('Invalid release version: ' . $version);
        }

        return $version;
    }

    /** @param array<string, string> $checksums */
    private function requireChecksum(array $checksums, string $name): string
    {
        if (!isset($checksums[$name])) {
            throw new RuntimeException('GitHub release is missing a SHA-256 digest for ' . $name);
        }

        return $checksums[$name];
    }

    private function assertHash(string $path, string $expectedHash, string $name): void
    {
        $hash = hash_file('sha256', $path);
        if ($hash === false || !hash_equals($expectedHash, strtolower($hash))) {
            throw new RuntimeException('SHA-256 mismatch for ' . $name);
        }
    }

    private function prepareInstallDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create install directory: ' . $directory);
        }
    }

    private function releaseDownloadUrl(string $version, string $asset): string
    {
        return self::GITHUB_DOWNLOAD_URL . rawurlencode($version) . '/' . rawurlencode($asset);
    }

    private function createTemporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . '/n98-fast-di-compile-' . bin2hex(random_bytes(16));
        if (!mkdir($directory, 0700)) {
            throw new RuntimeException('Could not create temporary directory.');
        }

        return $directory;
    }

    private function removeDirectory(string $directory): void
    {
        foreach (glob($directory . '/*') ?: [] as $path) {
            @unlink($path);
        }
        @rmdir($directory);
    }
}
