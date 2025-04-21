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
            ->setDescription('Download patch from github merge request <comment>(experimental)</comment>');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->diffContent = '';
        $this->repository = $input->getOption('repository');
        if ($input->getOption('mage-os')) {
            $this->repository = 'mage-os/mageos-magento2';
        }

        $pullRequestDataResponse = $this->getPullRequestInfoByApi($input);

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
            $output->write($this->fetchDiffContent($prData['diff_url']));

            return Command::SUCCESS;
        }

        // Show infos as fallback
        $table->render();

        if ($input->getOption('patch')) {
            $replaceVendor = 'magento';
            if ($input->getOption('mage-os')) {
                $replaceVendor = 'mage-os';
            }

            $patchFilename = $this->patchFile($prData, $replaceVendor, $output);

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
     * @return string
     */
    protected function fetchDiffContent($diffUrl): string
    {
        if ($this->diffContent === '') {
            $response = Requests::get($diffUrl, [], ['verify' => true]);
            $this->diffContent = $response->body;
        }

        return $this->diffContent;
    }

    /**
     * @param array $prData
     * @param string $replaceVendor
     * @param OutputInterface $output
     * @return string Patch file name
     */
    protected function patchFile(array $prData, string $replaceVendor, OutputInterface $output): string
    {
        $patchFileContent = PatchFileContentCreator::create(
            $this->fetchDiffContent($prData['diff_url']),
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

    /**
     * @param InputInterface $input
     * @return \WpOrg\Requests\Response
     */
    protected function getPullRequestInfoByApi(InputInterface $input): Response
    {
        $pullRequestDataResponse = Requests::get(
            sprintf(
                'https://api.github.com/repos/%s/pulls/%d.patch',
                $this->repository,
                $input->getArgument('number')
            ),
            [],
            ['verify' => true]
        );
        return $pullRequestDataResponse;
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
