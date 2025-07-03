<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearCommand
 * @package N98\Magento\Command\Developer\Asset
 */
class ClearCommand extends AbstractMagentoCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string[]
     */
    private $messages = [];

    /**
     * @param Filesystem $filesystem
     * @return void
     */
    public function inject(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $help = <<<EOT
Clears static view files.

To clear assets for all themes:

   $ n98-magerun2.phar dev:asset:clear

To clear assets for specific theme(s) only:

   $ n98-magerun2.phar dev:asset:clear --theme=Magento/luma

EOT;

        $this
            ->setName('dev:asset:clear')
            ->addOption(
                'theme',
                't',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Clear assets for specific theme(s) only'
            )
            ->setDescription('Clear static assets')
            ->setHelp($help);
    }

    /**
     * @param string $code
     * @return WriteInterface|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getDirectoryWrite($code)
    {
        /** @var WriteInterface $dir */
        $dir = $this->filesystem->getDirectoryWrite($code);
        $dirPath = $dir->getAbsolutePath();
        if (!$dir->isExist()) {
            $this->messages[] = '<warning>Directory "' . $dirPath . '" does not exist - skipped</warning>';

            return null;
        }

        return $dir;
    }

    /**
     * @param string $theme
     * @param string $code
     * @return string[]
     */
    private function findThemePaths($theme, $code)
    {
        $theme = '/' . trim($theme, '/');
        $themeLength = strlen($theme);

        /** @var Read $dir */
        $dir = $this->filesystem->getDirectoryRead($code);
        $entries = $dir->readRecursively('');
        $paths = [];
        foreach ($entries as $entry) {
            if (substr($entry, -$themeLength) === $theme &&
                $dir->isDirectory($entry)) {
                $paths[] = $entry;
            }
        }

        return $paths;
    }

    /**
     * @param WriteInterface $dir
     * @param string $path
     * @return void
     */
    private function deleteDirectory($dir, $path)
    {
        try {
            $dir->delete($path);
            $this->messages[] = '<info><comment>' . $dir->getAbsolutePath() . $path . '</comment> deleted</info>';
        } catch (\Exception $e) {
            $this->messages[] = '<error>' . $e->getMessage() . '</error>';
            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $this->messages[] = '<debug>' . $e->getTraceAsString() . '</debug>';
            }
        }
    }

    /**
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function emptyDirectory($code)
    {
        /** @var WriteInterface $dir */
        $dir = $this->getDirectoryWrite($code);
        if ($dir) {
            foreach ($dir->search('*') as $path) {
                if ($path !== '.' && $path !== '..') {
                    $this->deleteDirectory($dir, $path);
                }
            }
        }
    }

    /**
     * @param string $theme
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function deleteThemeDirectories($theme, $code)
    {
        /** @var WriteInterface $dir */
        $dir = $this->getDirectoryWrite($code);
        if ($dir) {
            $paths = $this->findThemePaths($theme, $code);
            foreach ($paths as $path) {
                $this->deleteDirectory($dir, $path);
            }
        }
    }

    /**
     * @param array $themes
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function clearThemes($themes)
    {
        foreach ($themes as $theme) {
            $this->deleteThemeDirectories($theme, DirectoryList::STATIC_VIEW);
            $this->deleteThemeDirectories($theme, DirectoryList::TMP_MATERIALIZATION_DIR);
        }
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function clearAllThemes()
    {
        $this->emptyDirectory(DirectoryList::STATIC_VIEW);
        $this->emptyDirectory(DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        if ($this->runsInProductionMode($input, $output)) {
            $output->writeln('This command is not available in production mode');
            return Command::FAILURE;
        }

        $this->output = $output;
        $themes = $input->getOption('theme');
        if ($themes) {
            $this->clearThemes($themes);
        } else {
            $this->clearAllThemes();
        }

        $output->writeln($this->messages);

        return Command::SUCCESS;
    }
}
