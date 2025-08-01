<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Media;

use Magento\Framework\App\Filesystem\DirectoryList;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

/**
 * Class DumpCommand
 * @package N98\Magento\Command\Media
 */
class DumpCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    protected function configure()
    {
        $this
            ->setName('media:dump')
            ->addOption('strip', '', InputOption::VALUE_NONE, 'Excludes image cache')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Dump filename')
            ->setDescription('Creates an archive with content of media folder.');
    }

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function inject(\Magento\Framework\Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandConfig = $this->getCommandConfig();

        $mediaDirectoryReader = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $this->detectMagento($output);
        $finder = Finder::create()
            ->files()
            ->followLinks()
            ->in($mediaDirectoryReader->getAbsolutePath());

        if ($input->getOption('strip')) {
            $finder->exclude($commandConfig['strip']['folders']);
        }

        $filename = (string) $input->getArgument('filename');

        if (is_dir($filename)) { // support for dot dir
            $filename = realpath($filename);
            $filename .= '/';
        }

        if (empty($filename) || is_dir($filename)) {
            $filename .= 'media_' . date('Ymd_his') . '.zip';
        }

        $zip = new ZipArchive();
        $zip->open($filename, ZIPARCHIVE::CREATE);
        $zip->addEmptyDir('media');
        $lastFolder = '';

        foreach ($finder as $file) {
            /* @var $file SplFileInfo */
            $currentFolder = pathinfo($file->getRelativePathname(), PATHINFO_DIRNAME);
            if ($currentFolder != $lastFolder) {
                $output->writeln(
                    sprintf('<info>Compress directory:</info> <comment>media/%s</comment>', $currentFolder)
                );
            }
            $zip->addFile($file->getPathname(), 'media' . DIRECTORY_SEPARATOR . $file->getRelativePathname());

            $lastFolder = $currentFolder;
        }

        $zip->close();

        return Command::SUCCESS;
    }
}
