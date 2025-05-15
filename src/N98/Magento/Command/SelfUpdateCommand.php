<?php

namespace N98\Magento\Command;

use Exception;
use N98\Util\Markdown\VersionFilePrinter;
use Phar;
use PharException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;
use WpOrg\Requests\Hooks;
use WpOrg\Requests\Requests;
use WpOrg\Requests\Transport\Curl as CurlTransport;

/**
 * @codeCoverageIgnore
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Christian Münch <c.muench@netz98.de>
 */
class SelfUpdateCommand extends AbstractMagentoCommand
{
    public const VERSION_TXT_URL_UNSTABLE         = 'https://raw.githubusercontent.com/netz98/n98-magerun2/develop/version.txt';
    public const MAGERUN_DOWNLOAD_URL_UNSTABLE    = 'https://files.magerun.net/n98-magerun2-dev.phar';
    public const VERSION_TXT_URL_STABLE           = 'https://raw.githubusercontent.com/netz98/n98-magerun2/master/version.txt';
    public const MAGERUN_DOWNLOAD_URL_STABLE      = 'https://files.magerun.net/n98-magerun2.phar';
    public const CHANGELOG_DOWNLOAD_URL_UNSTABLE  = 'https://raw.github.com/netz98/n98-magerun2/develop/CHANGELOG.md';
    public const CHANGELOG_DOWNLOAD_URL_STABLE    = 'https://raw.github.com/netz98/n98-magerun2/master/CHANGELOG.md';

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setAliases(['selfupdate'])
            ->addOption('unstable', null, InputOption::VALUE_NONE, 'Load unstable version from develop branch')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Tests if there is a new version without any update.')
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Version to update to. Can be used to rollback to a previous version.'
            )
            ->setDescription('Updates n98-magerun2.phar to the latest or a specified version.')
            ->setHelp(
                <<<EOT
The <info>self-update</info> command checks GitHub for newer
versions of n98-magerun2 and if found, installs the latest.

Optionally, you can specify a version to rollback or update to:

  <info>php n98-magerun2.phar self-update 7.4.0</info>

If you want the unstable (develop) branch, use:

  <info>php n98-magerun2.phar self-update --unstable</info>
EOT
            );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        // Only enable self-update if running in phar mode
        return $this->getApplication()->isPharMode();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDryRun      = $input->getOption('dry-run');
        $loadUnstable  = $input->getOption('unstable');
        $requestedVersion = $input->getArgument('version');
        $previousVersion = $this->getApplication()->getVersion();

        $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
        $tempFilename  = dirname($localFilename) . '/' . basename($localFilename, '.phar') . '-temp.phar';

        // check for permissions in local filesystem before start connection process
        if (!is_writable($tempDirectory = dirname($tempFilename))) {
            throw new RuntimeException(
                'n98-magerun2 update failed: the "' . $tempDirectory .
                '" directory used to download the temp file could not be written'
            );
        }

        if (!is_writable($localFilename)) {
            throw new RuntimeException(
                'n98-magerun2 update failed: the "' . $localFilename . '" file could not be written'
            );
        }

        // If the user wants the unstable (develop) version, just keep original logic
        if ($loadUnstable) {
            $versionTxtUrl         = self::VERSION_TXT_URL_UNSTABLE;
            $remotePharDownloadUrl = self::MAGERUN_DOWNLOAD_URL_UNSTABLE;

            // We directly fetch the latest “unstable” version from version.txt
            $response = Requests::get($versionTxtUrl, [], ['verify' => true]);
            if (!$response->success) {
                throw new RuntimeException('Cannot get version: ' . $response->status_code);
            }

            $latestVersion = trim($response->body);

            // Proceed with the normal logic, ignoring any specific version
            if ($this->isOutdatedVersion($latestVersion, $loadUnstable)) {
                $this->updatePhar(
                    $output,
                    $remotePharDownloadUrl,
                    $tempFilename,
                    $localFilename,
                    $isDryRun,
                    true
                );
            } else {
                $output->writeln("<info>You are using the latest n98-magerun2 unstable version.</info>");
            }
            return Command::SUCCESS;
        }

        // Otherwise (stable mode):
        // 1) If a specific version was provided, skip version.txt and build the download URL for that version
        // 2) If no version was provided, use the existing “latest stable version” logic
        if ($requestedVersion) {
            // Construct a download URL for that version. Example pattern might be: https://files.magerun.net/n98-magerun2-4.0.2.phar
            // Adapt the pattern to match how your releases are actually hosted.
            $remotePharDownloadUrl = sprintf(
                'https://files.magerun.net/n98-magerun2-%s.phar',
                $requestedVersion
            );

            // Check if the requested file exists via HEAD
            $existsCheck = Requests::head($remotePharDownloadUrl, [], ['verify' => true]);
            if (!$existsCheck->success) {
                throw new RuntimeException(
                    sprintf('Requested version "%s" could not be found at %s', $requestedVersion, $remotePharDownloadUrl)
                );
            }

            $output->writeln(
                sprintf("Rolling back/updating to specific version <info>%s</info>.", $requestedVersion)
            );

            // We do *not* check if it’s “outdated” – user explicitly wants that version.
            $this->updatePhar(
                $output,
                $remotePharDownloadUrl,
                $tempFilename,
                $localFilename,
                $isDryRun,
                false,
                $requestedVersion
            );
        } else {
            // Use the original “latest stable” logic via version.txt
            $versionTxtUrl         = self::VERSION_TXT_URL_STABLE;
            $remotePharDownloadUrl = self::MAGERUN_DOWNLOAD_URL_STABLE;

            $response = Requests::get($versionTxtUrl, [], ['verify' => true]);
            if (!$response->success) {
                throw new RuntimeException('Cannot get version: ' . $response->status_code);
            }

            $latestVersion = trim($response->body);

            if ($this->isOutdatedVersion($latestVersion, false)) {
                $output->writeln(
                    sprintf("Updating to the latest stable version <info>%s</info>.", $latestVersion)
                );
                $this->updatePhar(
                    $output,
                    $remotePharDownloadUrl,
                    $tempFilename,
                    $localFilename,
                    $isDryRun,
                    false
                );

                $output->writeln(sprintf(
                    '<info>If you want to rollback to version %s, you can run:</info>',
                    $previousVersion
                ));
                $output->writeln(sprintf(
                    '  <comment>php n98-magerun2.phar self-update %s</comment>',
                    $previousVersion
                ));
            } else {
                $output->writeln("<info>You are using the latest n98-magerun2 stable version.</info>");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Stop execution (Workaround to prevent warnings after replacing the phar file).
     */
    protected function _exit($statusCode = 0)
    {
        exit($statusCode);
    }

    /**
     * Helper method to download, validate, replace, and display changelog.
     */
    private function updatePhar(
        OutputInterface $output,
        string $remotePharDownloadUrl,
        string $tempFilename,
        string $localFilename,
        bool $isDryRun,
        bool $loadUnstable,
        string $forcedVersion = null
    ) {
        try {
            $this->downloadNewPhar($output, $remotePharDownloadUrl, $tempFilename);
            $this->checkNewPharFile($tempFilename, $localFilename);

            class_exists(VersionFilePrinter::class); // Ensure the class is loaded to prevent errors after phar file replacement

            if (!$isDryRun) {
                $this->replaceExistingPharFile($tempFilename, $localFilename);
            }

            if ($forcedVersion) {
                $output->writeln('<info>---------------------------------</info>');
                $output->writeln(
                    sprintf(
                        '<info>Successfully updated n98-magerun2 to version <comment>%s</comment></info>',
                        $forcedVersion
                    )
                );
            }

            if (!$forcedVersion) {
                $changelog = $this->getChangelog($output, $loadUnstable, $forcedVersion);
                $output->writeln('');
                $output->writeln('');
                $output->writeln($changelog);
                $output->writeln('<info>---------------------------------</info>');
                $output->writeln('<info>Successfully updated n98-magerun2</info>');
                $output->writeln('<info>---------------------------------</info>');
            }

            $this->_exit(0);
        } catch (Exception $e) {
            @unlink($tempFilename);
            if (!$e instanceof UnexpectedValueException && !$e instanceof PharException) {
                throw $e;
            }
            $output->writeln('<error>The download is corrupted (' . $e->getMessage() . ').</error>');
            $output->writeln('<error>Please re-run the self-update command to try again.</error>');
        }
    }

    /**
     * Download the phar using a retry loop with HTTP/1.1.
     */
    private function downloadNewPhar(OutputInterface $output, string $remoteUrl, string $tempFilename)
    {
        $maxRetries = 3;
        $retryDelaySeconds = 5;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            // Clean up any existing file before each attempt
            if (file_exists($tempFilename)) {
                unlink($tempFilename);
            }

            $output->writeln("<info>Fetching file information from {$remoteUrl} (attempt {$attempt}/{$maxRetries})...</info>");

            // Get file information
            $filesize = $this->getRemoteFileSize($output, $remoteUrl);

            // Setup progress bar
            $progress = $this->createProgressBar($output, $filesize);

            // Prepare cURL options
            $curlOpts = $this->prepareCurlOptions($output);

            try {
                // Perform the download
                $response = $this->performDownload($remoteUrl, $tempFilename, $progress, $curlOpts);

                $progress->finish();
                $output->writeln('');

                if (!$response->success) {
                    throw new RuntimeException("HTTP error: {$response->status_code}");
                }

                // Verify full file size if known
                if ($filesize > 0 && filesize($tempFilename) !== $filesize) {
                    throw new RuntimeException(
                        sprintf("Download incomplete: expected %d bytes, got %d bytes.", $filesize, filesize($tempFilename))
                    );
                }

                $output->writeln(
                    $attempt === 1
                    ? '<info>Successfully downloaded.</info>'
                    : "<info>Successfully downloaded after {$attempt} attempt(s).</info>"
                );

                return;
            } catch (\WpOrg\Requests\Exception $e) {
                $this->handleDownloadException($output, $e, $attempt, $maxRetries, $retryDelaySeconds, $tempFilename, $progress);

                if ($attempt >= $maxRetries) {
                    throw new RuntimeException("Download failed after {$maxRetries} attempts: {$e->getMessage()}");
                }
            } catch (Exception $e) {
                $this->handleDownloadException($output, $e, $attempt, $maxRetries, $retryDelaySeconds, $tempFilename, $progress);

                if ($attempt >= $maxRetries) {
                    throw new RuntimeException("Download failed after {$maxRetries} attempts: {$e->getMessage()}");
                }
            }
        }

        throw new RuntimeException("Download failed after {$maxRetries} attempts.");
    }

    /**
     * Get the size of the remote file.
     */
    private function getRemoteFileSize(OutputInterface $output, string $remoteUrl): int
    {
        $head = Requests::head($remoteUrl, [], [
            'verify'          => true,
            'timeout'         => 20,
            'connect_timeout' => 10,
        ]);

        $filesize = 0;
        if ($head->success && isset($head->headers['content-length'])) {
            $filesize = (int) $head->headers['content-length'];
        }

        return $filesize;
    }

    /**
     * Create a progress bar for the download.
     */
    private function createProgressBar(OutputInterface $output, int $filesize): ProgressBar
    {
        $progress = new ProgressBar($output);
        if ($filesize > 0) {
            $progress->setFormat('[%bar%] %current%/%max% bytes %percent:3s%% %elapsed:6s%/%estimated:-6s%');
            $progress->start($filesize);
        } else {
            $progress->setFormat('[%bar%] %current% bytes downloaded');
            $progress->start(0);
        }

        return $progress;
    }

    /**
     * Prepare cURL options for the download.
     */
    private function prepareCurlOptions(OutputInterface $output): array
    {
        // Prepare cURL options: force HTTP/1.1
        $curlOpts = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_BUFFERSIZE   => 128*1024, // 128 KB chunks instead of default 16 KB
            // Increase timeouts for large files
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 300,
        ];

        // Check for CURL_OPTS environment variable and apply those options
        $curlOptsEnv = getenv('CURL_OPTS');
        if ($curlOptsEnv) {
            $output->writeln("<info>Using cURL options from environment: {$curlOptsEnv}</info>");

            // Parse the options string (format: --http1.1 --connect-timeout 60 --max-time 300)
            $options = explode(' ', $curlOptsEnv);
            foreach ($options as $i => $option) {
                if (strpos($option, '--http1.1') === 0) {
                    $curlOpts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
                } elseif (strpos($option, '--connect-timeout') === 0 && isset($options[$i+1]) && is_numeric($options[$i+1])) {
                    $curlOpts[CURLOPT_CONNECTTIMEOUT] = (int) $options[$i+1];
                } elseif (strpos($option, '--max-time') === 0 && isset($options[$i+1]) && is_numeric($options[$i+1])) {
                    $curlOpts[CURLOPT_TIMEOUT] = (int) $options[$i+1];
                }
            }
        }

        return $curlOpts;
    }

    /**
     * Perform the download using the prepared options.
     */
    private function performDownload(string $remoteUrl, string $tempFilename, ProgressBar $progress, array $curlOpts)
    {
        $hooks = new Hooks();
        $hooks->register('requests.progress', function ($chunk, $downloaded, $total) use ($progress) {
            if ($progress->getMaxSteps() > 0) {
                $progress->setProgress($downloaded);
            }
        });

        // Use a proper Curl transport instance
        $transport = new CurlTransport();

        return Requests::get($remoteUrl, [], [
            'filename'        => $tempFilename,
            'blocking'        => true,
            'hooks'           => $hooks,
            'verify'          => true,
            'timeout'         => 300,
            'connect_timeout' => 60,
            'transport'       => $transport,
            'curl'            => $curlOpts,
        ]);
    }

    /**
     * Handle download exceptions.
     */
    private function handleDownloadException(
        OutputInterface $output,
        Exception $e,
        int $attempt,
        int $maxRetries,
        int $retryDelaySeconds,
        string $tempFilename,
        ProgressBar $progress
    ) {
        $progress->finish();
        $output->writeln('');

        $msg = $e->getMessage();

        // Clean up the temp file if it exists
        if (file_exists($tempFilename)) {
            unlink($tempFilename);
        }

        $output->writeln("<error>Download attempt {$attempt}/{$maxRetries} failed: {$msg}</error>");

        if ($attempt < $maxRetries) {
            $output->writeln("<comment>Waiting {$retryDelaySeconds} seconds before next attempt...</comment>");
            sleep($retryDelaySeconds);
        }
    }

    /**
     * Validate the downloaded phar file.
     */
    private function checkNewPharFile(string $tempFilename, string $localFilename)
    {
        error_reporting(E_ALL); // show all errors
        @chmod($tempFilename, 0777 & ~umask());

        // test the phar validity
        $phar = new Phar($tempFilename);
        unset($phar);
    }

    /**
     * Replace existing phar file with the newly downloaded one.
     */
    private function replaceExistingPharFile(string $tempFilename, string $localFilename)
    {
        if (!@rename($tempFilename, $localFilename)) {
            throw new RuntimeException(
                sprintf('Cannot replace existing phar file "%s". Please check permissions.', $localFilename)
            );
        }
    }

    /**
     * Download and format the changelog.
     *
     * @param OutputInterface $output
     * @param bool            $loadUnstable
     * @param string|null     $forcedVersion - the version we explicitly requested (if any)
     */
    private function getChangelog(
        OutputInterface $output,
        bool $loadUnstable,
        string $forcedVersion = null
    ): string {
        $changelog = '';

        // Use the normal stable or unstable changelog location
        $changeLogUrl = $loadUnstable
            ? self::CHANGELOG_DOWNLOAD_URL_UNSTABLE
            : self::CHANGELOG_DOWNLOAD_URL_STABLE;

        $response = Requests::get($changeLogUrl, [], ['verify' => true]);
        if (!$response->success) {
            throw new RuntimeException('Cannot download changelog: ' . $response->status_code);
        }

        $changeLogContent = $response->body;
        if ($changeLogContent) {
            $versionFilePrinter = new VersionFilePrinter($changeLogContent);

            // If we explicitly requested a version, just show all changes from current to that version
            // or you could skip the “previousVersion” logic if rolling back is unclear from the changelog.
            $previousVersion = $this->getApplication()->getVersion();
            $changelog      .= $versionFilePrinter->printFromVersion($previousVersion) . "\n";
        }

        // If it’s unstable, append dev warning
        if ($loadUnstable) {
            $unstableFooterMessage = <<<UNSTABLE_FOOTER
<comment>
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
!! DEVELOPMENT VERSION. DO NOT USE IN PRODUCTION !!
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
</comment>
UNSTABLE_FOOTER;
            $changelog .= $unstableFooterMessage . "\n";
        }

        return $changelog;
    }

    /**
     * Determine if we should update to the "latest" version or not.
     */
    private function isOutdatedVersion(string $latest, bool $loadUnstable): bool
    {
        // The check remains simple: if local != remote or we specifically want an unstable.
        return $this->getApplication()->getVersion() !== $latest || $loadUnstable;
    }
}
