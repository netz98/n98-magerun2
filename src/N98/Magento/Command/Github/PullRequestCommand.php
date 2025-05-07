<?php

namespace N98\Magento\Command\Github;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Github\PatchFileContent\Creator as PatchFileContentCreator;
use N98\Util\OperatingSystem;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use WpOrg\Requests\Requests;
use WpOrg\Requests\Response;

class PullRequestCommand extends AbstractMagentoCommand
{
    private string $diffContent = '';

    private string $repository = '';

    protected function configure()
    {
        $this->setName('github:pr')
            ->addArgument(
                'number',
                InputArgument::REQUIRED,
                'Pull Request Number'
            )
            ->addOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'Repository to fetch from', 'magento/magento2')
            ->addOption('mage-os', null, InputOption::VALUE_NONE, 'Shortcut option to use the mage-os/mageos-magento2 repository.')
            ->addOption('patch', 'd', InputOption::VALUE_NONE, 'Download patch and prepare it for applying')
            ->addOption('apply', 'a', InputOption::VALUE_NONE, 'Apply patch to current working directory')
            ->addOption('diff', null, InputOption::VALUE_NONE, 'Raw diff download')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Show pull request data as json')
            ->addOption(
                'github-token',
                null,
                InputOption::VALUE_OPTIONAL,
                'Github API token to avoid rate limits (can also be set via ENV variable GITHUB_TOKEN)'
            )
            ->setDescription('Download patch from github merge request <comment>(experimental)</comment>');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->diffContent = '';
        $this->repository = $input->getOption('repository');
        if ($input->getOption('mage-os')) {
            $this->repository = 'mage-os/mageos-magento2';
        }

        $pullRequestDataResponse = $this->getPullRequestInfoByApi($input, $output);

        if ($input->getOption('json')) {
            $output->writeln($pullRequestDataResponse->body);

            return Command::SUCCESS;
        }

        $prData = $pullRequestDataResponse->decode_body(true);

        if (!isset($prData['id'])) {
            $output->writeln('<error>Could not fetch pull request data</error>');

            return Command::FAILURE;
        }

        $table = PullRequestInfoTable::create($output, $prData);

        /**
         * Show only diff
         */
        if ($input->getOption('diff')) {
            $output->write($this->fetchDiffContent($prData['diff_url'], $input));

            return Command::SUCCESS;
        }

        // Show infos as fallback
        $table->render();

        if ($input->getOption('patch')) {
            $replaceVendor = 'magento';
            if ($input->getOption('mage-os')) {
                $replaceVendor = 'mage-os';
            }

            $patchFilename = $this->patchFile($prData, $replaceVendor, $output, $input);

            if ($input->getOption('apply')) {
                $this->applyPatch($output, $patchFilename);
            }
        }

        if (!$input->getOption('patch') && !$input->getOption('diff')) {
            $output->writeln('Use <comment>--patch</comment> to download the patch as ready to apply patch file');
            $output->writeln('Use <comment>--patch --apply</comment> to download the patch and directly apply it');
            $output->writeln('Use <comment>--diff</comment> to see the raw diff');
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $diffUrl
     * @param InputInterface $input
     * @return string
     */
    protected function fetchDiffContent(string $diffUrl, InputInterface $input): string
    {
        if ($this->diffContent === '') {
            $headers = $this->getGithubApiHeaders($input);
            $response = Requests::get($diffUrl, $headers, ['verify' => true]);
            $this->diffContent = $response->body;
        }

        return $this->diffContent;
    }

    /**
     * @param array $prData
     * @param string $replaceVendor
     * @param OutputInterface $output
     * @param InputInterface $input
     * @return string Patch file name
     */
    protected function patchFile(array $prData, string $replaceVendor, OutputInterface $output, InputInterface $input): string
    {
        $patchFileContent = PatchFileContentCreator::create(
            $this->fetchDiffContent($prData['diff_url'], $input),
            $replaceVendor
        );

        $filename = sprintf(
            'PR-%d-%s.patch',
            $prData['number'],
            str_replace('/', '-', $prData['base']['repo']['full_name'])
        );

        chdir(OperatingSystem::getCwd());
        if (file_put_contents($filename, $patchFileContent) === false) {
            throw new RuntimeException('Could not write patch file');
        }

        $output->writeln(sprintf('<info>Patch file created:</info> <comment>%s</comment>', $filename));

        return $filename;
    }

    protected function getPullRequestInfoByApi(InputInterface $input, OutputInterface $output, int $maxRetries = 5, int $baseDelaySeconds = 2): Response
    {
        $headers = $this->getGithubApiHeaders($input);
        $url = sprintf(
            'https://api.github.com/repos/%s/pulls/%d',
            $this->repository,
            $input->getArgument('number')
        );
        $retryCount = 0;

        while (true) {
            try {
                $response = Requests::get($url, $headers, ['verify' => true]);

                if ($response->status_code >= 200 && $response->status_code < 300) {
                    return $response;
                }

                if ($response->status_code === 403 && isset($response->headers['X-RateLimit-Remaining']) && $response->headers['X-RateLimit-Remaining'] === '0') {
                    $retryCount++;
                    if ($retryCount > $maxRetries) {
                        throw new RuntimeException(
                            sprintf('GitHub API rate limit exceeded (403) after %d retries. Last response: %s', $maxRetries, $response->body)
                        );
                    }
                    $resetTimestamp = $response->headers['X-RateLimit-Reset'] ?? time() + $baseDelaySeconds * (2 ** ($retryCount - 1));
                    $waitTime = (int) ($resetTimestamp - time());
                    if ($waitTime < 1) {
                        $waitTime = $baseDelaySeconds * (2 ** ($retryCount - 1));
                    }
                    $output->writeln(
                        sprintf('<warning>GitHub API rate limit hit (403). Waiting for %d seconds before retrying...</warning>', $waitTime)
                    );
                    sleep($waitTime);
                    continue;
                }

                if ($response->status_code === 429) {
                    $retryCount++;
                    if ($retryCount > $maxRetries) {
                        throw new RuntimeException(
                            sprintf('GitHub API secondary rate limit exceeded (429) after %d retries. Last response: %s', $maxRetries, $response->body)
                        );
                    }
                    $retryAfter = $response->headers['Retry-After'] ?? $baseDelaySeconds * (2 ** ($retryCount - 1));
                    $output->writeln(
                        sprintf('<warning>GitHub API secondary rate limit hit (429). Retrying in %d seconds...</warning>', $retryAfter)
                    );
                    sleep((int) $retryAfter);
                    continue;
                }

                // Other errors, don't retry immediately
                throw new RuntimeException(
                    sprintf('Error fetching pull request info. Status code: %d, Response: %s', $response->status_code, $response->body)
                );

            } catch (\WpOrg\Requests\Exception $e) {
                $retryCount++;
                if ($retryCount > $maxRetries) {
                    throw new RuntimeException(
                        sprintf('Network error during GitHub API request after %d retries: %s', $maxRetries, $e->getMessage())
                    );
                }
                $waitTime = $baseDelaySeconds * (2 ** ($retryCount - 1));
                $this->output->writeln(
                    sprintf('<warning>Network error during GitHub API request. Retrying in %d seconds...</warning>', $waitTime)
                );
                sleep($waitTime);
            }
        }
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    private function getGithubApiHeaders(InputInterface $input): array
    {
        $headers = [];
        $token = $input->getOption('github-token') ?: getenv('GITHUB_TOKEN');
        if ($token) {
            $headers['Authorization'] = 'token ' . $token;
        }
        return $headers;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $patchFilename
     * @return void
     */
    protected function applyPatch(OutputInterface $output, string $patchFilename): void
    {
        $output->writeln('<info>Applying patch...</info>');

        $process = new Process(['patch', '-p1']);
        $process->setInput(file_get_contents($patchFilename));
        $process->setTimeout(3600);
        $process->setWorkingDirectory(OperatingSystem::getCwd());
        $process->start();
        $process->wait(function ($type, $buffer) use ($output) {
            $output->write('<info>patch > </info><comment>' . $buffer . '</comment>', false);
        });
    }
}
