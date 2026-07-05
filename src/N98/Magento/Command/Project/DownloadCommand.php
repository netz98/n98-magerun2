<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Project;

use InvalidArgumentException;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\ComposerHelper;
use N98\Util\OperatingSystem;
use N98\Util\ProcessArguments;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class DownloadCommand extends AbstractMagentoCommand
{
    private const DEFAULT_EDITIONS = [
        'open-source' => [
            'package' => 'magento/project-community-edition',
            'repository-url' => 'https://repo.magento.com',
            'requires_auth' => true,
            'default_dir' => './magento-open-source',
            'description' => 'Community Edition — free, requires a Marketplace account for repo.magento.com',
        ],
        'adobe-commerce' => [
            'package' => 'magento/project-enterprise-edition',
            'repository-url' => 'https://repo.magento.com',
            'requires_auth' => true,
            'default_dir' => './adobe-commerce',
            'description' => 'Commerce Edition — commercial license, requires Marketplace credentials',
        ],
        'mage-os' => [
            'package' => 'mage-os/project-community-edition',
            'repository-url' => 'https://repo.mage-os.org',
            'requires_auth' => false,
            'default_dir' => './mage-os',
            'description' => 'Mage-OS',
        ],
    ];

    private const DEFAULT_GIT_REPOSITORIES = [
        'magento2' => [
            'label' => 'Magento 2 (official core)',
            'url' => 'https://github.com/magento/magento2.git',
        ],
        'mage-os' => [
            'label' => 'Mage-OS (community fork)',
            'url' => 'https://github.com/mage-os/mage-os.git',
        ],
    ];

    private const CUSTOM_GIT_REPO_KEY = 'custom';

    protected function configure(): void
    {
        $this
            ->setName('download')
            ->setDescription('Downloads Magento source code (composer create-project or git clone)')
            ->addOption(
                'strategy',
                null,
                InputOption::VALUE_REQUIRED,
                'Download strategy: composer or git [default: "composer"]'
            )
            ->addOption(
                'edition',
                null,
                InputOption::VALUE_REQUIRED,
                'Edition to download (composer strategy): ' . implode(', ', array_keys(self::DEFAULT_EDITIONS))
                . '. Configurable/extendable via config.yaml.'
            )
            ->addOption(
                'constraint',
                null,
                InputOption::VALUE_REQUIRED,
                'Version constraint, e.g. 2.4.7 (composer strategy). NOTE: not named "--version"/"-V" because '
                . 'those are reserved by the framework.'
            )
            ->addOption(
                'repository-url',
                null,
                InputOption::VALUE_REQUIRED,
                'Override composer repository URL used for selected edition (composer strategy)'
            )
            ->addOption(
                'repo',
                null,
                InputOption::VALUE_REQUIRED,
                'Git repository to clone (git strategy). Accepts a full git URL or a GitHub "owner/repo" shorthand.'
            )
            ->addOption(
                'branch',
                null,
                InputOption::VALUE_REQUIRED,
                'Branch, tag or ref to check out (git strategy). Defaults to repository default branch.'
            )
            ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Target directory to download into'
            )
            ->setHelp(
                <<<HELP
Downloads Magento source code into a target directory. This command only fetches source code -
it does not check platform requirements, generate configuration files or set up the database.
For highly customized Mage-OS builds use the external <comment>mageos-maker</comment> tool.

Interactive wizard (prompts for anything not passed as an option):

  $ n98-magerun2.phar download

The interactive wizard offers a preset picker for the git strategy (e.g. <comment>magento/magento2</comment>,
<comment>mage-os/mage-os</comment>) plus a custom entry accepting a full git URL or a GitHub
<comment>owner/repo</comment> shorthand, and asks for confirmation with a summary before starting the
download. All of this is skipped automatically when <comment>--no-interaction</comment> is used.

Non-interactive composer-based download:

  $ n98-magerun2.phar download --edition=mage-os --constraint=2.2.0 --dir=./my-shop --no-interaction

Non-interactive git-based download (for Magento core contributors), using the GitHub shorthand:

  $ n98-magerun2.phar download --strategy=git --repo=my-fork/magento2 \\
        --dir=./core-contribution --no-interaction
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $interactive = $input->isInteractive();

        if ($interactive) {
            $io->title('Magento Download Wizard');
        }

        $strategy = $input->getOption('strategy');
        if (!$strategy) {
            if ($interactive) {
                $io->text([
                    'This wizard downloads Magento (Open Source), Adobe Commerce, or Mage-OS source code. It does',
                    'not install dependencies beyond what the chosen strategy performs, check platform',
                    'requirements, or configure the application.',
                    '',
                    ' • <comment>composer</comment> — runs "composer create-project" for a known edition;',
                    '   installs a ready-to-use application into vendor/ (recommended for shops/projects).',
                    ' • <comment>git</comment>       — runs "git clone" against a preset or custom repository;',
                    '   for Magento core / Mage-OS contributors who need a working git checkout.',
                    '',
                    'Pass --no-interaction together with all required options to skip every prompt (see --help).',
                ]);

                $strategy = select(
                    'Download strategy',
                    [
                        'composer' => '<fg=cyan;options=bold>Composer</> create-project — installs a ready-to-use'
                            . ' Magento application (recommended)',
                        'git' => '<fg=cyan;options=bold>Git clone</> — for Magento core / Mage-OS contributors'
                            . ' who need a working git checkout',
                    ],
                    'composer'
                );
            } else {
                $strategy = 'composer';
            }
        }

        if (!in_array($strategy, ['composer', 'git'], true)) {
            throw new RuntimeException(sprintf('Invalid strategy "%s". Use "composer" or "git".', $strategy));
        }

        if ($strategy === 'git') {
            return $this->downloadWithGit($input, $io, $interactive);
        }

        return $this->downloadWithComposer($input, $io, $interactive);
    }

    private function downloadWithComposer(InputInterface $input, SymfonyStyle $io, bool $interactive): int
    {
        $editions = $this->getEditions();

        $edition = $input->getOption('edition');
        if (!$edition) {
            if (!$interactive) {
                throw new RuntimeException(
                    'The "--edition" option is required in non-interactive mode. Provide one of: '
                    . implode(', ', array_keys($editions)) . '.'
                );
            }
            $edition = select('Edition', $this->buildEditionChoices($editions), array_key_first($editions));
        }

        if (!isset($editions[$edition])) {
            throw new RuntimeException(sprintf(
                'Invalid edition "%s". Use one of: %s.',
                $edition,
                implode(', ', array_keys($editions))
            ));
        }

        $editionConfig = $editions[$edition];
        $repositoryUrl = $input->getOption('repository-url') ?: $editionConfig['repository-url'];

        if ($editionConfig['requires_auth']
            && $repositoryUrl
            && strpos($repositoryUrl, 'repo.magento.com') !== false
        ) {
            $this->ensureMagentoConnectCredentials($input, $io, $interactive);
        }

        $composerHelper = $this->getComposerHelper();
        $composerBin = $composerHelper->getBinPath();
        if ($composerBin === '') {
            throw new RuntimeException(
                'Could not find composer. Install it from https://getcomposer.org/ and ensure it is on your PATH.'
            );
        }

        $availableVersions = [];
        if ($repositoryUrl !== '') {
            if ($interactive) {
                $io->text(sprintf('Checking available versions for %s...', $editionConfig['package']));
            }
            $availableVersions = $this->fetchAvailableVersions($composerBin, $editionConfig['package'], (string) $repositoryUrl);
        }

        $version = $input->getOption('constraint');
        if ($version === null) {
            $version = $interactive
                ? suggest(
                    'Version constraint (leave empty for latest stable)',
                    self::sortVersionsDescending(self::filterStableVersions($availableVersions))
                )
                : '';
        }

        if ($version !== ''
            && $availableVersions !== []
            && self::looksLikeExactVersion((string) $version)
            && !in_array($version, $availableVersions, true)
        ) {
            throw new RuntimeException(sprintf(
                'Version "%s" is not available for "%s". Available versions include: %s.',
                $version,
                $editionConfig['package'],
                implode(', ', array_slice(self::sortVersionsDescending(self::filterStableVersions($availableVersions)), 0, 10))
            ));
        }

        $dir = $this->resolveTargetDirectory($input, $interactive, $editionConfig['default_dir']);
        $this->assertTargetUsable($dir);

        $proceed = $this->confirmProceed($io, $interactive, [
            ['Strategy', 'composer'],
            ['Edition', $edition],
            ['Composer package', $editionConfig['package']],
            ['Version constraint', $version !== '' ? $version : '(latest stable)'],
            ['Repository URL', $repositoryUrl !== '' ? (string) $repositoryUrl : '(none)'],
            ['Requires authentication', $editionConfig['requires_auth'] ? 'Yes' : 'No'],
            ['Target directory', $dir],
        ]);

        if (!$proceed) {
            $io->note('Download cancelled.');

            return Command::SUCCESS;
        }

        $exitCode = $this->runComposerCreateProject(
            $composerBin,
            $editionConfig['package'],
            (string) $repositoryUrl,
            (string) $version,
            $dir,
            $io
        );

        if ($exitCode === Command::SUCCESS) {
            $this->printPostInstallHint($io, $editionConfig['package'], $dir);
        }

        return $exitCode;
    }

    /**
     * Suggests the right next command to set up the downloaded application. Mage-OS 3.0 replaced the
     * classic multi-step "setup:install" flow with a single interactive "bin/magento install" wizard.
     */
    private function printPostInstallHint(SymfonyStyle $io, string $package, string $dir): void
    {
        if (str_starts_with($package, 'mage-os/')) {
            $installedVersion = $this->resolveInstalledMageOsVersion($dir);
            if ($installedVersion !== null && version_compare($installedVersion, '3.0.0', '>=')) {
                $io->note([
                    'Mage-OS 3.0+ ships a new interactive installer.',
                    'Run "bin/magento install" inside the project directory to set up the application.',
                ]);

                return;
            }
        }

        $io->note('Next step: run "bin/magento setup:install" inside the project directory to set up the application.');
    }

    /**
     * Reads the exact resolved Mage-OS version that "composer create-project" pinned into the
     * generated composer.json's "mage-os/product-community-edition" requirement.
     */
    protected function resolveInstalledMageOsVersion(string $dir): ?string
    {
        $composerJsonPath = rtrim($dir, '/') . '/composer.json';
        if (!is_file($composerJsonPath)) {
            return null;
        }

        $composerJson = json_decode((string) file_get_contents($composerJsonPath), true);
        $version = is_array($composerJson) ? ($composerJson['require']['mage-os/product-community-edition'] ?? null) : null;

        return is_string($version) ? ltrim($version, 'v') : null;
    }

    /**
     * Queries composer for the real list of available versions of a package on a repository.
     * Doubles as a credential/entitlement pre-flight check: an authenticated repository with
     * invalid or unentitled credentials fails here exactly the way "composer create-project"
     * would fail later, just earlier and with a more actionable error message.
     *
     * @return list<string>
     */
    protected function fetchAvailableVersions(string $composerBin, string $package, string $repositoryUrl): array
    {
        $manifest = tempnam(sys_get_temp_dir(), 'n98-magerun2-composer-');
        if ($manifest === false) {
            throw new RuntimeException('Could not create a temporary composer manifest file.');
        }

        file_put_contents($manifest, (string) json_encode([
            'repositories' => [
                ['type' => 'composer', 'url' => $repositoryUrl],
            ],
        ]));

        try {
            $process = new Process(
                [$composerBin, 'show', $package, '--all', '--format=json', '--no-interaction'],
                null,
                ['COMPOSER' => $manifest]
            );
            $process->setTimeout(30);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException(sprintf(
                    'Could not find package "%s" on "%s". This usually means your Marketplace '
                    . 'credentials are invalid or expired, or your account is not entitled to this '
                    . 'edition. Verify your keys at https://marketplace.magento.com/customer/accessKeys/.',
                    $package,
                    $repositoryUrl
                ));
            }

            $data = json_decode($process->getOutput(), true);
        } finally {
            unlink($manifest);
        }

        $versions = is_array($data) ? ($data['versions'] ?? null) : null;

        return is_array($versions) ? array_values(array_filter($versions, 'is_string')) : [];
    }

    private static function looksLikeExactVersion(string $value): bool
    {
        return preg_match('/^[\d.]+(-\w+)?$/', $value) === 1;
    }

    /**
     * @param list<string> $versions
     * @return list<string>
     */
    private static function filterStableVersions(array $versions): array
    {
        return array_values(array_filter($versions, static function (string $version): bool {
            return !str_starts_with($version, 'dev-')
                && preg_match('/(dev|alpha|beta|rc)\b/i', $version) !== 1;
        }));
    }

    /**
     * @param list<string> $versions
     * @return list<string>
     */
    private static function sortVersionsDescending(array $versions): array
    {
        usort($versions, static fn (string $a, string $b): int => version_compare($b, $a));

        return $versions;
    }

    /**
     * @return array<string, array{package: string, repository-url: string, requires_auth: bool, default_dir: string, description: string}>
     */
    protected function getEditions(): array
    {
        $configuredEditions = $this->getCommandConfig()['editions'] ?? [];
        if ($configuredEditions === []) {
            return self::DEFAULT_EDITIONS;
        }

        $editions = [];
        foreach ($configuredEditions as $editionConfig) {
            $name = $editionConfig['name'];
            $editions[$name] = [
                'package' => $editionConfig['package'],
                'repository-url' => $editionConfig['repository-url'] ?? '',
                'requires_auth' => $editionConfig['requires-auth'] ?? false,
                'default_dir' => $editionConfig['default-dir'] ?? ('./' . $name),
                'description' => $editionConfig['description'] ?? '',
            ];
        }

        return $editions;
    }

    /**
     * @param array<string, array{package: string, repository-url: string, requires_auth: bool, default_dir: string, description: string}> $editions
     * @return array<string, string>
     */
    private function buildEditionChoices(array $editions): array
    {
        $choices = [];
        foreach ($editions as $name => $editionConfig) {
            $description = $editionConfig['description'] ?? '';
            $choices[$name] = $description !== ''
                ? $description
                : sprintf('Composer package: %s', $editionConfig['package']);
        }

        return $choices;
    }

    /**
     * @return array<string, array{label: string, url: string}>
     */
    protected function getGitRepositories(): array
    {
        $configuredRepositories = $this->getCommandConfig()['git-repositories'] ?? [];
        if ($configuredRepositories === []) {
            return self::DEFAULT_GIT_REPOSITORIES;
        }

        $repositories = [];
        foreach ($configuredRepositories as $repositoryConfig) {
            $name = $repositoryConfig['name'];
            $repositories[$name] = [
                'label' => $repositoryConfig['label'] ?? $name,
                'url' => $repositoryConfig['url'],
            ];
        }

        return $repositories;
    }

    /**
     * Normalizes a git URL or a GitHub "owner/repo" shorthand into a clonable URL.
     */
    public static function normalizeGitRepoUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Please enter a git URL or a GitHub "owner/repo" shorthand.');
        }

        // Full URL (scheme://...) or SCP-like SSH syntax (user@host:path) -> use as-is.
        if (preg_match('#^([a-z][a-z0-9+.-]*://|[\w.-]+@[\w.-]+:)#i', $value) === 1) {
            return $value;
        }

        // GitHub "owner/repo" shorthand, optionally with a trailing ".git".
        if (preg_match('#^([\w.-]+)/([\w.-]+?)(?:\.git)?$#', $value, $matches) === 1) {
            return sprintf('https://github.com/%s/%s.git', $matches[1], $matches[2]);
        }

        throw new InvalidArgumentException(sprintf(
            'Could not understand "%s". Enter a full git URL (e.g. https://github.com/owner/repo.git or '
            . 'git@github.com:owner/repo.git) or a GitHub "owner/repo" shorthand.',
            $value
        ));
    }

    protected function runComposerCreateProject(
        string $composerBin,
        string $package,
        string $repositoryUrl,
        string $version,
        string $dir,
        SymfonyStyle $io
    ): int {
        $args = new ProcessArguments([$composerBin, 'create-project']);
        if ($repositoryUrl !== '') {
            $args->addArgs(['repository-url' => $repositoryUrl]);
        }
        $args->addArg('--no-dev')
            ->addArg($package)
            ->addArg($dir);

        if ($version !== '') {
            $args->addArg($version);
        }

        if (OutputInterface::VERBOSITY_VERBOSE <= $io->getVerbosity()) {
            $args->addArg('-vvv');
        }

        $process = $args->createProcess();
        $process->setTimeout(86400);
        $process->start();
        $code = $process->wait(function ($type, $buffer) use ($io): void {
            $io->write($buffer, false, OutputInterface::OUTPUT_RAW);
        });

        if ($code !== 0) {
            $io->error(sprintf('composer create-project failed (exit code %d).', $code));

            return Command::FAILURE;
        }

        $io->success(sprintf('Successfully downloaded to %s', $dir));

        return Command::SUCCESS;
    }

    private function ensureMagentoConnectCredentials(
        InputInterface $input,
        SymfonyStyle $io,
        bool $interactive
    ): void {
        $configKey = 'http-basic.repo.magento.com';
        $composerHelper = $this->getComposerHelper();
        $authConfig = $composerHelper->getConfigValue($configKey);

        if (isset($authConfig->username, $authConfig->password)) {
            return;
        }

        if (!$interactive) {
            throw new RuntimeException(
                'This edition requires repo.magento.com credentials. Configure them in auth.json '
                . '(or COMPOSER_AUTH) before running in non-interactive mode.'
            );
        }

        $io->note([
            'You need a Marketplace security key. Login at https://marketplace.magento.com/customer/accessKeys/.',
            'My Profile -> Access Keys. Use the public key as username and the private key as password.',
        ]);

        $username = text(
            'Please enter your public key',
            validate: fn ($value) => $value === '' ? 'The public key (auth token) can not be empty' : null
        );

        $password = password(
            'Please enter your private key',
            validate: fn ($value) => $value === '' ? 'The private key (auth token) can not be empty' : null
        );

        $composerHelper->setConfigValue($configKey, [$username, $password]);
    }

    private function downloadWithGit(InputInterface $input, SymfonyStyle $io, bool $interactive): int
    {
        $repo = $this->resolveGitRepo($input, $interactive);

        $branch = $input->getOption('branch');
        if ($branch === null) {
            $branch = $interactive
                ? text('Branch/tag/ref (leave empty for default branch)')
                : '';
        }

        $dir = $this->resolveTargetDirectory($input, $interactive, './magento2');
        $this->assertTargetUsable($dir);

        if (!OperatingSystem::isProgramInstalled('git')) {
            $io->error('git is not installed or not on PATH.');

            return Command::FAILURE;
        }

        $proceed = $this->confirmProceed($io, $interactive, [
            ['Strategy', 'git'],
            ['Repository', $repo],
            ['Branch/ref', $branch !== '' ? $branch : '(repository default)'],
            ['Target directory', $dir],
        ]);

        if (!$proceed) {
            $io->note('Download cancelled.');

            return Command::SUCCESS;
        }

        return $this->runGitClone($repo, (string) $branch, $dir, $io);
    }

    private function resolveGitRepo(InputInterface $input, bool $interactive): string
    {
        $repo = $input->getOption('repo');
        if ($repo) {
            try {
                return self::normalizeGitRepoUrl((string) $repo);
            } catch (InvalidArgumentException $exception) {
                throw new RuntimeException($exception->getMessage(), 0, $exception);
            }
        }

        if (!$interactive) {
            throw new RuntimeException('The "--repo" option is required in non-interactive mode.');
        }

        $repositories = $this->getGitRepositories();
        $choices = [];
        foreach ($repositories as $name => $repositoryConfig) {
            $choices[$name] = $repositoryConfig['label'];
        }
        $choices[self::CUSTOM_GIT_REPO_KEY] = 'Custom — enter a full git URL or GitHub "owner/repo" shorthand';

        $selected = select('Repository', $choices, array_key_first($choices));

        if ($selected === self::CUSTOM_GIT_REPO_KEY) {
            return self::normalizeGitRepoUrl(text('Git URL or GitHub "owner/repo"'));
        }

        return $repositories[$selected]['url'];
    }

    protected function runGitClone(string $repo, string $branch, string $dir, SymfonyStyle $io): int
    {
        $cmd = ['git', 'clone'];
        if ($branch !== '') {
            $cmd[] = '--branch';
            $cmd[] = $branch;
        }
        $cmd[] = $repo;
        $cmd[] = $dir;

        $process = new Process($cmd);
        $process->setTimeout(86400);
        $process->run(function ($type, $buffer) use ($io): void {
            $io->write($buffer, false, OutputInterface::OUTPUT_RAW);
        });

        if (!$process->isSuccessful()) {
            $io->error('git clone failed.');

            return Command::FAILURE;
        }

        $io->success(sprintf('Successfully cloned into %s', $dir));

        return Command::SUCCESS;
    }

    private function resolveTargetDirectory(
        InputInterface $input,
        bool $interactive,
        string $default
    ): string {
        $dir = $input->getOption('dir');
        if (!$dir) {
            if (!$interactive) {
                throw new RuntimeException('The "--dir" option is required in non-interactive mode.');
            }
            $dir = text('Target directory', default: $default);
        }

        return rtrim((string) $dir, '/');
    }

    /**
     * @param list<array{0: string, 1: string}> $summaryRows
     */
    private function confirmProceed(SymfonyStyle $io, bool $interactive, array $summaryRows): bool
    {
        if (!$interactive) {
            return true;
        }

        $io->section('Summary');
        $io->table(['Setting', 'Value'], $summaryRows);

        return confirm('Proceed with the download?', true);
    }

    private function assertTargetUsable(string $dir): void
    {
        if (file_exists($dir) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Target "%s" exists and is not a directory.', $dir));
        }

        if (is_dir($dir)) {
            $entries = array_diff((array) scandir($dir), ['.', '..']);
            if (count($entries) > 0) {
                throw new RuntimeException(sprintf(
                    'Target directory "%s" already exists and is not empty. Choose an empty or non-existent'
                    . ' directory.',
                    $dir
                ));
            }
        }
    }

    private function getComposerHelper(): ComposerHelper
    {
        /** @var ComposerHelper $composerHelper */
        $composerHelper = $this->getHelper('composer');

        return $composerHelper;
    }
}
