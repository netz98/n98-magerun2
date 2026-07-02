<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Project;

use InvalidArgumentException;
use N98\Util\Console\Helper\ComposerHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadCommandTest extends TestCase
{
    /**
     * @var string[]
     */
    private $tempDirs = [];

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->removeDirectory($dir);
        }
        $this->tempDirs = [];
    }

    public function testComposerNonInteractiveSuccessForNoAuthEdition(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $exitCode = $tester->execute([
            '--edition' => 'mage-os',
            '--constraint' => '2.2.0',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, $composerCalls);
        $this->assertSame('mage-os/project-community-edition', $composerCalls[0]['package']);
        $this->assertSame('https://repo.mage-os.org', $composerCalls[0]['repositoryUrl']);
        $this->assertSame('2.2.0', $composerCalls[0]['version']);
        $this->assertSame($dir, $composerCalls[0]['dir']);
    }

    public function testComposerNonInteractiveSuccessForAuthenticatedEdition(): void
    {
        $composerCalls = [];
        $authConfig = (object) ['username' => 'pub', 'password' => 'priv'];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, $authConfig);
        $dir = $this->makeTempPath();

        $exitCode = $tester->execute([
            '--edition' => 'adobe-commerce',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);
        $this->assertSame('magento/project-enterprise-edition', $composerCalls[0]['package']);
        $this->assertSame('https://repo.magento.com', $composerCalls[0]['repositoryUrl']);
        $this->assertSame('', $composerCalls[0]['version']);
    }

    public function testComposerMissingCredentialsFailsInNonInteractiveMode(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, (object) []);
        $dir = $this->makeTempPath();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('repo.magento.com credentials');

        $tester->execute([
            '--edition' => 'open-source',
            '--dir' => $dir,
        ], ['interactive' => false]);
    }

    public function testGitNonInteractiveSuccess(): void
    {
        $gitCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $exitCode = $tester->execute([
            '--strategy' => 'git',
            '--repo' => 'git@github.com:my-fork/magento2.git',
            '--branch' => '2.4-develop',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, $gitCalls);
        $this->assertSame('git@github.com:my-fork/magento2.git', $gitCalls[0]['repo']);
        $this->assertSame('2.4-develop', $gitCalls[0]['branch']);
        $this->assertSame($dir, $gitCalls[0]['dir']);
    }

    public function testGitNonInteractiveSuccessWithoutBranch(): void
    {
        $gitCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $tester->execute([
            '--strategy' => 'git',
            '--repo' => 'https://github.com/magento/magento2.git',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertSame('', $gitCalls[0]['branch']);
    }

    public function testMissingEditionFailsInNonInteractiveMode(): void
    {
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('"--edition" option is required');

        $tester->execute([
            '--dir' => $this->makeTempPath(),
        ], ['interactive' => false]);
    }

    public function testMissingRepoFailsInNonInteractiveMode(): void
    {
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('"--repo" option is required');

        $tester->execute([
            '--strategy' => 'git',
            '--dir' => $this->makeTempPath(),
        ], ['interactive' => false]);
    }

    public function testMissingDirFailsInNonInteractiveMode(): void
    {
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('"--dir" option is required');

        $tester->execute([
            '--edition' => 'mage-os',
        ], ['interactive' => false]);
    }

    public function testInvalidEditionFailsInNonInteractiveMode(): void
    {
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid edition "bogus"');

        $tester->execute([
            '--edition' => 'bogus',
            '--dir' => $this->makeTempPath(),
        ], ['interactive' => false]);
    }

    public function testInteractiveModePromptsOnlyForMissingDirectory(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $tester->setInputs([$dir, '']);
        $exitCode = $tester->execute([
            '--strategy' => 'composer',
            '--edition' => 'mage-os',
            '--constraint' => '2.2.0',
        ], ['interactive' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame($dir, $composerCalls[0]['dir']);
        $this->assertSame('mage-os/project-community-edition', $composerCalls[0]['package']);
    }

    public function testInteractiveModePromptsForStrategy(): void
    {
        $gitCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $tester->setInputs(['git', 'custom', 'https://github.com/my-fork/magento2.git', '', $dir, '']);
        $exitCode = $tester->execute([], ['interactive' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, $gitCalls);
        $this->assertSame('https://github.com/my-fork/magento2.git', $gitCalls[0]['repo']);
        $this->assertSame($dir, $gitCalls[0]['dir']);
    }

    public function testTargetDirectoryNotEmptyIsRejected(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already exists and is not empty');

        try {
            $tester->execute([
                '--edition' => 'mage-os',
                '--dir' => $dir,
            ], ['interactive' => false]);
        } finally {
            $this->assertCount(0, $composerCalls);
        }
    }

    public function testComposerNotFoundFailsClearly(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find composer');

        $tester->execute([
            '--edition' => 'mage-os',
            '--dir' => $this->makeTempPath(),
        ], ['interactive' => false]);
    }

    public function testGitPresetSelectionUsesConfiguredUrl(): void
    {
        $gitCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $tester->setInputs(['git', 'mage-os', '', $dir, '']);
        $exitCode = $tester->execute([], ['interactive' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, $gitCalls);
        $this->assertSame('https://github.com/mage-os/mage-os.git', $gitCalls[0]['repo']);
    }

    public function testGitCustomShorthandIsNormalized(): void
    {
        $gitCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $tester->setInputs(['git', 'custom', 'my-fork/magento2', '', $dir, '']);
        $exitCode = $tester->execute([], ['interactive' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame('https://github.com/my-fork/magento2.git', $gitCalls[0]['repo']);
    }

    public function testConfirmationDeclineCancelsDownload(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $tester->setInputs([$dir, 'no']);
        $exitCode = $tester->execute([
            '--strategy' => 'composer',
            '--edition' => 'mage-os',
            '--constraint' => '2.2.0',
        ], ['interactive' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(0, $composerCalls);
        $this->assertStringContainsString('cancelled', $tester->getDisplay());
    }

    public function testConfirmationAcceptRunsDownload(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true);
        $dir = $this->makeTempPath();

        $tester->setInputs([$dir, 'yes']);
        $exitCode = $tester->execute([
            '--strategy' => 'composer',
            '--edition' => 'mage-os',
            '--constraint' => '2.2.0',
        ], ['interactive' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, $composerCalls);
    }

    public function testComposerRejectsUnavailableExactVersion(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, ['1.0.0', '2.2.0']);
        $dir = $this->makeTempPath();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not available');

        $tester->execute([
            '--edition' => 'mage-os',
            '--constraint' => '9.9.9',
            '--dir' => $dir,
        ], ['interactive' => false]);
    }

    public function testComposerAcceptsAvailableExactVersion(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, ['1.0.0', '2.2.0']);
        $dir = $this->makeTempPath();

        $exitCode = $tester->execute([
            '--edition' => 'mage-os',
            '--constraint' => '2.2.0',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, $composerCalls);
    }

    public function testComposerAllowsConstraintRangesWithoutValidation(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, ['1.0.0', '2.2.0']);
        $dir = $this->makeTempPath();

        $exitCode = $tester->execute([
            '--edition' => 'mage-os',
            '--constraint' => '^2.4',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);
        $this->assertCount(1, $composerCalls);
        $this->assertSame('^2.4', $composerCalls[0]['version']);
    }

    public function testAvailableVersionsSuggestionIsSortedDescending(): void
    {
        $composerCalls = [];
        $tester = $this->createTester(
            $composerCalls,
            $gitCalls,
            0,
            0,
            true,
            null,
            ['1.0.0', '2.4.10', '2.2.0', '2.4.7']
        );
        $dir = $this->makeTempPath();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Available versions include: 2.4.10, 2.4.7, 2.2.0, 1.0.0.');

        $tester->execute([
            '--edition' => 'mage-os',
            '--constraint' => '9.9.9',
            '--dir' => $dir,
        ], ['interactive' => false]);
    }

    public function testComposerFetchFailurePresentsClearErrorMessage(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, null, true);
        $dir = $this->makeTempPath();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('entitled');

        $tester->execute([
            '--edition' => 'adobe-commerce',
            '--dir' => $dir,
        ], ['interactive' => false]);
    }

    public function testInteractiveModePromptsForVersionUsingAvailableVersions(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, ['1.0.0', '2.2.0']);
        $dir = $this->makeTempPath();

        $tester->setInputs(['mage-os', '2.2.0', $dir, '']);
        $exitCode = $tester->execute([
            '--strategy' => 'composer',
        ], ['interactive' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame('2.2.0', $composerCalls[0]['version']);
    }

    public function testMageOsThreeOrNewerSuggestsBinMagentoInstall(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, null, false, '3.1.0');
        $dir = $this->makeTempPath();

        $tester->execute([
            '--edition' => 'mage-os',
            '--constraint' => '3.1.0',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertStringContainsString('bin/magento install', $tester->getDisplay());
        $this->assertStringNotContainsString('setup:install', $tester->getDisplay());
    }

    public function testMageOsBelowThreeSuggestsSetupInstall(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, null, false, '2.2.0');
        $dir = $this->makeTempPath();

        $tester->execute([
            '--edition' => 'mage-os',
            '--constraint' => '2.2.0',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertStringContainsString('bin/magento setup:install', $tester->getDisplay());
    }

    public function testOtherEditionsAlwaysSuggestSetupInstall(): void
    {
        $composerCalls = [];
        $tester = $this->createTester($composerCalls, $gitCalls, 0, 0, true, null, null, false, '3.1.0');
        $dir = $this->makeTempPath();

        $tester->execute([
            '--edition' => 'adobe-commerce',
            '--dir' => $dir,
        ], ['interactive' => false]);

        $this->assertStringContainsString('bin/magento setup:install', $tester->getDisplay());
    }

    /**
     * @dataProvider gitRepoUrlProvider
     */
    public function testNormalizeGitRepoUrl(string $input, string $expected): void
    {
        $this->assertSame($expected, DownloadCommand::normalizeGitRepoUrl($input));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function gitRepoUrlProvider(): array
    {
        return [
            'full https url' => [
                'https://github.com/magento/magento2.git',
                'https://github.com/magento/magento2.git',
            ],
            'ssh scp-style url' => [
                'git@github.com:magento/magento2.git',
                'git@github.com:magento/magento2.git',
            ],
            'shorthand' => [
                'magento/magento2',
                'https://github.com/magento/magento2.git',
            ],
            'shorthand with .git suffix' => [
                'magento/magento2.git',
                'https://github.com/magento/magento2.git',
            ],
        ];
    }

    public function testNormalizeGitRepoUrlRejectsEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DownloadCommand::normalizeGitRepoUrl('   ');
    }

    public function testNormalizeGitRepoUrlRejectsMalformedValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DownloadCommand::normalizeGitRepoUrl('not a valid repo');
    }

    /**
     * @param array<int, array<string, mixed>> $composerCalls
     * @param array<int, array<string, mixed>> $gitCalls
     */
    private function createTester(
        &$composerCalls,
        &$gitCalls,
        int $composerExitCode = 0,
        int $gitExitCode = 0,
        bool $composerAvailable = true,
        ?object $authConfig = null,
        ?array $availableVersions = null,
        bool $fetchVersionsFails = false,
        ?string $installedMageOsVersion = null
    ): CommandTester {
        $composerCalls = [];
        $gitCalls = [];
        $authConfig = $authConfig ?? (object) ['username' => 'pub', 'password' => 'priv'];

        $command = new class($composerCalls, $gitCalls, $composerExitCode, $gitExitCode, $availableVersions ?? [], $fetchVersionsFails, $installedMageOsVersion) extends DownloadCommand {
            private $composerCallsRef;

            private $gitCallsRef;

            private $composerExitCode;

            private $gitExitCode;

            private $availableVersions;

            private $fetchVersionsFails;

            private $installedMageOsVersion;

            public function __construct(
                array &$composerCalls,
                array &$gitCalls,
                int $composerExitCode,
                int $gitExitCode,
                array $availableVersions,
                bool $fetchVersionsFails,
                ?string $installedMageOsVersion
            ) {
                parent::__construct();
                $this->composerCallsRef = &$composerCalls;
                $this->gitCallsRef = &$gitCalls;
                $this->composerExitCode = $composerExitCode;
                $this->gitExitCode = $gitExitCode;
                $this->availableVersions = $availableVersions;
                $this->fetchVersionsFails = $fetchVersionsFails;
                $this->installedMageOsVersion = $installedMageOsVersion;
            }

            protected function shouldFallbackToPlainPrompts(): bool
            {
                return true;
            }

            protected function fetchAvailableVersions(string $composerBin, string $package, string $repositoryUrl): array
            {
                if ($this->fetchVersionsFails) {
                    throw new RuntimeException(sprintf(
                        'Could not find package "%s" on "%s". This usually means your Marketplace '
                        . 'credentials are invalid or expired, or your account is not entitled to this '
                        . 'edition. Verify your keys at https://marketplace.magento.com/customer/accessKeys/.',
                        $package,
                        $repositoryUrl
                    ));
                }

                return $this->availableVersions;
            }

            protected function resolveInstalledMageOsVersion(string $dir): ?string
            {
                return $this->installedMageOsVersion;
            }

            protected function getEditions(): array
            {
                return [
                    'open-source' => [
                        'package' => 'magento/project-community-edition',
                        'repository-url' => 'https://repo.magento.com',
                        'requires_auth' => true,
                        'default_dir' => './magento-open-source',
                    ],
                    'adobe-commerce' => [
                        'package' => 'magento/project-enterprise-edition',
                        'repository-url' => 'https://repo.magento.com',
                        'requires_auth' => true,
                        'default_dir' => './adobe-commerce',
                    ],
                    'mage-os' => [
                        'package' => 'mage-os/project-community-edition',
                        'repository-url' => 'https://repo.mage-os.org',
                        'requires_auth' => false,
                        'default_dir' => './mage-os',
                    ],
                ];
            }

            protected function getGitRepositories(): array
            {
                return [
                    'magento2' => [
                        'label' => 'Magento 2 (official core)',
                        'url' => 'https://github.com/magento/magento2.git',
                    ],
                    'mage-os' => [
                        'label' => 'Mage-OS (community fork)',
                        'url' => 'https://github.com/mage-os/mage-os.git',
                    ],
                ];
            }

            protected function runComposerCreateProject(
                string $composerBin,
                string $package,
                string $repositoryUrl,
                string $version,
                string $dir,
                SymfonyStyle $output
            ): int {
                $this->composerCallsRef[] = compact('composerBin', 'package', 'repositoryUrl', 'version', 'dir');

                return $this->composerExitCode;
            }

            protected function runGitClone(string $repo, string $branch, string $dir, SymfonyStyle $output): int
            {
                $this->gitCallsRef[] = compact('repo', 'branch', 'dir');

                return $this->gitExitCode;
            }
        };

        $application = new Application();
        $application->add($command);

        $composerHelper = new class($composerAvailable, $authConfig) extends ComposerHelper {
            private $available;

            private $authConfig;

            public function __construct(bool $available, object $authConfig)
            {
                $this->available = $available;
                $this->authConfig = $authConfig;
            }

            public function getBinPath()
            {
                return $this->available ? 'composer' : '';
            }

            public function getConfigValue($key, $useGlobalConfig = true)
            {
                return $this->authConfig;
            }

            public function setConfigValue($key, $values, $useGlobalConfig = true)
            {
                return '';
            }
        };
        $application->getHelperSet()->set($composerHelper, 'composer');

        /** @var DownloadCommand $foundCommand */
        $foundCommand = $application->find('download');

        return new CommandTester($foundCommand);
    }

    private function makeTempPath(bool $withFile = false): string
    {
        $dir = sys_get_temp_dir() . '/n98-magerun2-download-test-' . uniqid();
        if ($withFile) {
            mkdir($dir, 0777, true);
            file_put_contents($dir . '/existing.txt', 'x');
        }
        $this->tempDirs[] = $dir;

        return $dir;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = array_diff((array) scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
