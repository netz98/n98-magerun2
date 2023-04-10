<?php

namespace N98\Magento\Command\Github;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use WpOrg\Requests\Requests;

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
            ->addOption('diff', null, InputOption::VALUE_NONE, 'Raw diff download')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Show pull request data as json')
            ->setDescription('Download patch from github merge request');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->diffContent = '';
        $this->repository = $input->getOption('repository');
        if ($input->getOption('mage-os')) {
            $this->repository = 'mage-os/mageos-magento2';
        }

        $pullRequestDataResponse = Requests::get(
            sprintf(
                'https://api.github.com/repos/%s/pulls/%d.patch',
                $this->repository,
                $input->getArgument('number')
            )
        );

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
            $patchFileContent = PatchFileContentCreator::create($prData, $this->fetchDiffContent($prData['diff_url']));

            $filename = sprintf(
                'PR-%d-%s.patch',
                $prData['number'],
                str_replace('/', '-', $prData['base']['repo']['full_name'])
            );

            chdir(OperatingSystem::getCwd());
            if (file_put_contents($filename, $patchFileContent) === false) {
                throw new \RuntimeException('Could not write patch file');
            }

            $output->writeln(sprintf('<info>Patch file created:</info> <comment>%s</comment>', $filename));
        }

        if (!$input->getOption('patch') && !$input->getOption('diff')) {
            $output->writeln('Use <comment>--patch</comment> to download the patch as ready to apply patch file');
            $output->writeln('Use <comment>--diff</comment> to see the raw diff');
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $diff_url
     * @return string
     */
    protected function fetchDiffContent($diff_url): string
    {
        if ($this->diffContent === '') {
            $response = Requests::get($diff_url);
            $this->diffContent = $response->body;
        }

        return $this->diffContent;
    }
}
