<?php

namespace N98\Magento\Command\Developer\Report;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CountCommand extends AbstractMagentoCommand
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function configure()
    {
        $this
            ->setName('dev:report:count')
            ->setDescription('Get count of report files');
    }

    /**
     * @param DirectoryList $directoryList
     */
    public function inject(
        DirectoryList $directoryList,
        Filesystem $filesystem
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        if (!$directoryRead->isDirectory('report')) {
            $count = 0; // currently we have no error report
        } else {
            $count = $this->getFileCount($directoryRead->getAbsolutePath('report'));
        }

        $output->writeln($count);
    }

    /**
     * Returns the number of files in the directory.
     *
     * @param string $path Path to the directory
     * @return int
     */
    protected function getFileCount($path)
    {
        return Finder::create()
            ->files()
            ->depth(1)
            ->ignoreUnreadableDirs(true)
            ->in($path)
            ->count();
    }
}
