<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Log;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

/**
 * Class SizeCommand
 * @package N98\Magento\Command\Developer\Log
 */
class SizeCommand extends AbstractMagentoCommand
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
            ->setName('dev:log:size')
            ->setDescription('Get size of log files')
            ->addOption(
                'sort-by-size',
                's',
                InputOption::VALUE_NONE,
                'Sort by file size (largest first)'
            )
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_REQUIRED,
                'Filter log files by name pattern'
            )
            ->addOption(
                'human-readable',
                'H',
                InputOption::VALUE_NONE,
                'Show file sizes in human readable format'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setHelp(
                'This command displays the size of all log files in the var/log directory. ' .
                'Magento 2 has various log files like system.log, debug.log, exception.log, etc.'
            );
    }

    /**
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     */
    public function inject(
        DirectoryList $directoryList,
        Filesystem $filesystem
    ) {
        $this->directoryList = $directoryList;
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
        $this->detectMagento($output);

        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $logPath = $directoryRead->getAbsolutePath('log');

        if (!$directoryRead->isDirectory('log')) {
            $output->writeln('<info>No log directory found at var/log</info>');
            return Command::SUCCESS;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.log')
            ->ignoreUnreadableDirs(true)
            ->in($logPath);

        // Apply filter if provided
        $filter = $input->getOption('filter');
        if ($filter) {
            $finder->name('*' . $filter . '*');
        }

        $logFiles = [];
        $totalSize = 0;

        foreach ($finder as $file) {
            $size = $file->getSize();
            $totalSize += $size;

            $logFiles[] = [
                'name' => $file->getBasename(),
                'size' => $size,
                'path' => $file->getRelativePathname(),
                'modified' => $file->getMTime()
            ];
        }

        if (empty($logFiles)) {
            $filterMessage = $filter ? " matching filter '$filter'" : '';
            $output->writeln("<info>No log files found{$filterMessage}</info>");
            return Command::SUCCESS;
        }

        // Sort files
        if ($input->getOption('sort-by-size')) {
            usort($logFiles, function ($a, $b) {
                return $b['size'] <=> $a['size']; // Largest first
            });
        } else {
            usort($logFiles, function ($a, $b) {
                return $a['name'] <=> $b['name']; // Alphabetical
            });
        }

        $humanReadable = $input->getOption('human-readable');
        $format = $input->getOption('format');

        $rows = [];
        foreach ($logFiles as $file) {
            $size = $humanReadable ? $this->formatBytes($file['size']) : $file['size'];
            $modified = date('Y-m-d H:i:s', $file['modified']);
            $rows[] = [
                $file['name'],
                $size,
                $modified
            ];
        }

        $headers = ['Log File', 'Size', 'Last Modified'];
        if ($format) {
            $this->getHelper('table')
                ->setHeaders($headers)
                ->renderByFormat($output, $rows, $format);
        } else {
            $table = new Table($output);
            $table->setHeaders($headers);
            foreach ($rows as $row) {
                $table->addRow($row);
            }
            $table->render();
        }

        // Display summary
        $fileCount = count($logFiles);
        $totalSizeFormatted = $humanReadable ? $this->formatBytes($totalSize) : $totalSize;
        $filterMessage = $filter ? " (filtered by '$filter')" : '';

        $output->writeln('');
        $output->writeln(sprintf(
            '<info>Total: %d log files%s, %s</info>',
            $fileCount,
            $filterMessage,
            $totalSizeFormatted
        ));

        return Command::SUCCESS;
    }

    /**
     * Format bytes into human readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
