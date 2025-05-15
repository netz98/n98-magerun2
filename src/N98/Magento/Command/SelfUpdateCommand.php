<?php

namespace N98\Magento\Command;

use Exception;
use N98\Util\Http\CurlClient;
use N98\Util\Http\DownloadException;
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
        // Check if curl extension is available
        if (!extension_loaded('curl')) {
            $output->writeln('<error>The curl extension is not available. Please install or enable it.</error>');
            return Command::FAILURE;
        }

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
            $response = CurlClient::curlGet($versionTxtUrl);
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
            $existsCheck =  CurlClient::curlHead($remotePharDownloadUrl);
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

            $response = CurlClient::curlGet($versionTxtUrl);
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

            // Prepare request options
            $requestOpts = $this->prepareRequestOptions($output);

            try {
                // Perform the download
                $response = $this->performDownload($remoteUrl, $tempFilename, $progress, $requestOpts);

                $progress->finish();
                $output->writeln('');

                if (!$response->success) {
                    throw new RuntimeException(
                        "Download failed despite HTTP {$response->status_code} error: {$response->error}"
                    );
                }

                // Verify full file size if known
                $actualFileSize = filesize($tempFilename);
                if ($filesize > 0 && $actualFileSize !== $filesize) {
                    // Log detailed information about the partial download
                    $output->writeln(sprintf(
                        "<error>Download incomplete: expected %d bytes, got %d bytes.</error>",
                        $filesize,
                        $actualFileSize
                    ));

                    // If we have detailed curl info, log it for debugging
                    if (isset($response->curl_info)) {
                        $output->writeln("<comment>Download details:</comment>");
                        $output->writeln(sprintf(
                            "  - HTTP Status: %d",
                            $response->status_code
                        ));
                        $output->writeln(sprintf(
                            "  - Content-Length: %d",
                            $response->content_length ?? 'unknown'
                        ));
                        $output->writeln(sprintf(
                            "  - Size downloaded: %d",
                            $response->size_download ?? 'unknown'
                        ));
                        if (isset($response->curl_errno) && $response->curl_errno !== 0) {
                            $output->writeln(sprintf(
                                "  - Curl error: %d - %s",
                                $response->curl_errno,
                                $response->error ?? 'unknown'
                            ));
                        }
                    }

                    throw new RuntimeException("Download incomplete: partial file received");
                }

                // Validate the downloaded file is a valid PHAR
                try {
                    // Try to open the PHAR file to validate it
                    $phar = new Phar($tempFilename);
                    unset($phar); // Close the PHAR
                } catch (UnexpectedValueException $e) {
                    $output->writeln("<error>Downloaded file is not a valid PHAR: {$e->getMessage()}</error>");
                    throw new RuntimeException("Invalid PHAR file downloaded");
                }

                $output->writeln(
                    $attempt === 1
                    ? '<info>Successfully downloaded.</info>'
                    : "<info>Successfully downloaded after {$attempt} attempt(s).</info>"
                );

                return;
            } catch (Exception $e) {
                // Create a custom exception with the remote URL for the alternative download method
                $exception = new DownloadException($e->getMessage(), $remoteUrl, $e->getCode(), $e);

                $this->handleDownloadException($output, $exception, $attempt, $maxRetries, $retryDelaySeconds, $tempFilename, $progress);

                if ($attempt >= $maxRetries) {
                    // Extract HTTP status code from error message if present
                    if (preg_match('/HTTP (\d+)/', $e->getMessage(), $matches)) {
                        $statusCode = (int)$matches[1];
                        // For HTTP 200-299 range, provide a clearer error message
                        if ($statusCode >= 200 && $statusCode < 300) {
                            throw new RuntimeException("Download failed after {$maxRetries} attempts despite receiving HTTP {$statusCode} (OK) responses. Check network connectivity and file integrity.");
                        }
                    }
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
        $head =  CurlClient::curlHead($remoteUrl, [], [
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
     * Prepare request options for the download.
     */
    private function prepareRequestOptions(OutputInterface $output): array
    {
        $requestOpts = [
            // Increase timeouts for large files
            'connect_timeout' => 60,
            'timeout' => 600, // 10 minutes
            // Disable timeout completely for large file downloads
            'disable_timeout' => true,
        ];

        $output->writeln('<info>Using extended timeout settings for large file download</info>');

        return $requestOpts;
    }

    /**
     * Perform the download using the prepared options.
     */
    private function performDownload(string $remoteUrl, string $tempFilename, ProgressBar $progress, array $requestOpts)
    {
        // Create a progress callback for curl
        $progressCallback = function ($curlResource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($progress) {
            if ($downloadSize > 0 && $progress->getMaxSteps() > 0) {
                $progress->setProgress($downloaded);
            } elseif ($downloaded > 0) {
                // If we don't know the total size, just update with current downloaded bytes
                $progress->setProgress($downloaded);
            }
            return 0; // Return 0 to continue the transfer
        };

        // Prepare headers for the request
        $headers = [
            'User-Agent' => 'n98-magerun2',
            'Accept'     => 'application/octet-stream',
            'Accept-Encoding' => 'gzip, deflate',
        ];

        // Prepare options for the request
        $options = array_merge($requestOpts, [
            'filename' => $tempFilename,
            'progress_callback' => $progressCallback,
        ]);

        try {
            // Perform the request
            return CurlClient::curlGet($remoteUrl, $headers, $options);
        } catch (Exception $e) {
            // Convert exceptions to RuntimeException for compatibility
            throw new RuntimeException($e->getMessage());
        }
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
        $downloadedSize = 0;
        $isPartialDownload = false;
        $isTransferClosedError = false;
        $bytesRemaining = 0;

        // Check for "transfer closed with X bytes remaining to read" error
        if (preg_match('/transfer closed with (\d+) bytes remaining to read/', $msg, $matches)) {
            $isTransferClosedError = true;
            $bytesRemaining = (int)$matches[1];
            $output->writeln("<comment>Transfer closed error detected: $bytesRemaining bytes remaining</comment>");
            $output->writeln("<comment>This is likely due to a network interruption or server timeout.</comment>");
        }

        // Check if the file exists and has content
        if (file_exists($tempFilename)) {
            $downloadedSize = filesize($tempFilename);
            $isPartialDownload = $downloadedSize > 0;

            if ($isPartialDownload) {
                $output->writeln("<comment>Partial download detected: $downloadedSize bytes</comment>");

                if ($isTransferClosedError) {
                    $totalSize = $downloadedSize + $bytesRemaining;
                    $percentComplete = round(($downloadedSize / $totalSize) * 100, 2);
                    $output->writeln("<comment>Download was approximately $percentComplete% complete before interruption.</comment>");
                    $output->writeln("<comment>Attempting to resume download from position $downloadedSize...</comment>");
                }
            }
        }

        // Special handling for transfer errors with partial downloads
        if ($isPartialDownload) {
            try {
                // Try to validate the file as a valid PHAR
                error_reporting(E_ALL); // show all errors
                @chmod($tempFilename, 0777 & ~umask());

                // Test the phar validity by opening it and checking its signature
                try {
                    // First try with normal Phar opening
                    $phar = new Phar($tempFilename);
                    unset($phar);

                    // If we get here, the PHAR is valid despite the download error
                    $output->writeln("<info>File appears to be a valid PHAR despite download error. Proceeding.</info>");
                    return;
                } catch (UnexpectedValueException $pharException) {
                    // If it fails with UnexpectedValueException, it's likely a corrupted PHAR
                    $output->writeln("<error>Downloaded file is not a valid PHAR: {$pharException->getMessage()}</error>");

                    // Try more aggressive approaches on the last retry
                    if ($attempt >= $maxRetries) {
                        // First, try a completely different approach - use file_get_contents instead of curl
                        $output->writeln("<comment>Attempting alternative download method...</comment>");

                        try {
                            // Set a longer timeout for large files
                            $headers = [
                                'User-Agent: n98-magerun2',
                                'Accept: application/octet-stream',
                            ];

                            // Add Range header if we have a partial download
                            if ($isPartialDownload) {
                                $headers[] = 'Range: bytes=' . $downloadedSize . '-';
                                $output->writeln("<comment>Resuming download from byte position $downloadedSize</comment>");
                            }

                            $context = stream_context_create([
                                'http' => [
                                    'timeout' => 600, // 10 minutes timeout
                                    'header' => $headers,
                                ],
                                'ssl' => [
                                    'verify_peer' => true,
                                    'verify_peer_name' => true,
                                ],
                            ]);

                            $remoteUrl = $e instanceof DownloadException ? $e->remoteUrl : '';
                            $output->writeln("<comment>Attempting to download from $remoteUrl</comment>");

                            // Try to download the file using file_get_contents
                            $fileContent = @file_get_contents($remoteUrl, false, $context);

                            if ($fileContent !== false) {
                                // Write the content to the temp file
                                // If we're resuming a download, append to the existing file
                                $writeMode = ($isPartialDownload && isset($headers) && in_array('Range: bytes=' . $downloadedSize . '-', $headers))
                                    ? FILE_APPEND
                                    : 0;

                                if (file_put_contents($tempFilename, $fileContent, $writeMode) !== false) {
                                    $output->writeln("<info>Alternative download method successful.</info>");

                                    // Validate the downloaded file
                                    try {
                                        $phar = new Phar($tempFilename);
                                        unset($phar);
                                        $output->writeln("<info>Downloaded file is a valid PHAR. Proceeding.</info>");
                                        return;
                                    } catch (Exception $e2) {
                                        $output->writeln("<error>Downloaded file is not a valid PHAR: {$e2->getMessage()}</error>");
                                    }
                                }
                            }
                        } catch (Exception $e2) {
                            $output->writeln("<error>Alternative download method failed: {$e2->getMessage()}</error>");
                        }

                        // If alternative method failed, try to repair the existing partial download
                        $output->writeln("<comment>Attempting to repair the downloaded PHAR file...</comment>");

                        // Read the file to find the PHAR signature
                        $fileContent = file_get_contents($tempFilename);
                        $sigPos = strpos($fileContent, 'GBMB'); // PHAR signature marker

                        if ($sigPos !== false) {
                            // Found a potential PHAR signature, truncate the file
                            $output->writeln("<comment>Found potential PHAR signature at position $sigPos</comment>");
                            file_put_contents($tempFilename, substr($fileContent, 0, $sigPos + 4));

                            // Try to validate again
                            try {
                                $phar = new Phar($tempFilename);
                                unset($phar);
                                $output->writeln("<info>Successfully repaired PHAR file. Proceeding.</info>");
                                return;
                            } catch (Exception $e2) {
                                $output->writeln("<error>Repair attempt failed: {$e2->getMessage()}</error>");
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $output->writeln("<error>Error validating PHAR: {$e->getMessage()}</error>");
            }
        }

        // Clean up the temp file if it exists
        if (file_exists($tempFilename)) {
            unlink($tempFilename);
        }

        $output->writeln("<error>Download attempt {$attempt}/{$maxRetries} failed: {$msg}</error>");

        if ($attempt < $maxRetries) {
            // Increase the delay for each retry attempt
            $currentDelay = $retryDelaySeconds * $attempt;
            $output->writeln("<comment>Waiting {$currentDelay} seconds before next attempt...</comment>");
            sleep($currentDelay);
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

        $response = CurlClient::curlGet($changeLogUrl);
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

    /**
     * Make a HEAD request using curl.
     *
     * @param string $url The URL to request
     * @param array $headers Optional headers to send with the request
     * @param array $options Optional curl options
     * @return object Response object with success, status_code, and headers properties
     */
    private function curlHead(string $url, array $headers = [], array $options = []): object
    {
        return CurlClient::curlHead($url, $headers, $options);
    }
}
