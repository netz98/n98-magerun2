<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use N98\Magento\Command\Developer\Console\Structure\ThemeNameStructure;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeThemeCommand
 * @package N98\Magento\Command\Developer\Console
 */
class MakeThemeCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:theme')
            ->addArgument('area', InputArgument::REQUIRED, 'Area like "frontend"')
            ->addArgument('package', InputArgument::REQUIRED, 'Package like "Vendor"')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the theme')
            ->addOption(
                'modules-base-dir',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Directory where theme should be created. Default is app/design if not reconfigured'
            )
            ->setDescription('Creates a new theme');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->get(Filesystem::class);
        /** @var $filesystem \Magento\Framework\Filesystem */
        $appDirReader = $filesystem->getDirectoryRead(DirectoryList::APP);
        $appDirWriter = $filesystem->getDirectoryWrite(DirectoryList::APP);

        $themeName = new ThemeNameStructure(
            $input->getArgument('area'),
            $input->getArgument('package'),
            $input->getArgument('name')
        );

        $relativePath = 'design' . '/' . $themeName;

        if (!$appDirReader->isDirectory($relativePath)) {
            $appDirWriter->create($relativePath);
            $output->writeln('<info>generated </info><comment>' . $relativePath . '</comment>');
        }

        if (!$appDirReader->isFile($relativePath . '/registration.php')) {
            $this->createRegistrationFile($themeName, $appDirWriter, $relativePath);
        }

        if (!$appDirReader->isFile($relativePath . '/composer.json')) {
            $this->createComposerFile($themeName, $appDirWriter, $relativePath);
        }

        if (!$appDirReader->isFile($relativePath . '/theme.xml')) {
            $this->createThemeXmlFile(
                $output,
                $appDirWriter,
                $relativePath,
                ucfirst($input->getArgument('package')) . ' ' . $input->getArgument('name')
            );
        }

        if (!$appDirReader->isFile($relativePath . '/etc/view.xml')) {
            $this->createViewXmlFile(
                $output,
                $appDirWriter,
                $relativePath
            );
        }

        return Command::SUCCESS;
    }

    /**
     * @param ThemeNameStructure $themeName
     * @param \Magento\Framework\Filesystem\Directory\WriteInterface $appDirWriter
     * @param string $relativePath
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function createRegistrationFile(ThemeNameStructure $themeName, $appDirWriter, $relativePath)
    {
        $registrationFileBody = <<<FILE_BODY
<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::THEME,
    '{$themeName->__toString()}',
    __DIR__
);

FILE_BODY;
        $appDirWriter->writeFile($relativePath . '/registration.php', $registrationFileBody);
    }

    /**
     * @param ThemeNameStructure $themeName
     * @param WriteInterface $appDirectoryWriter
     * @param string $relativePath
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function createComposerFile(ThemeNameStructure $themeName, WriteInterface $appDirectoryWriter, $relativePath)
    {
        $composerFileBody = $this->getHelper('twig')->render(
            'dev/console/make/theme/composer.json.twig',
            [
                'theme' => $themeName,
            ]
        );

        $appDirectoryWriter->writeFile(
            $relativePath . '/composer.json',
            $composerFileBody
        );
    }

    /**
     * @param OutputInterface $output
     * @param \Magento\Framework\Filesystem\Directory\WriteInterface $appDirWriter
     * @param string $relativePath
     * @param string $name
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function createThemeXmlFile(OutputInterface $output, $appDirWriter, $relativePath, $name)
    {
        $xmlContent = <<<XML_CONTENT
<?xml version="1.0"?>
<theme xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/theme.xsd">
    <title>$name</title>
    <parent>Magento/blank</parent>
    <media>
        <preview_image>media/preview.jpg</preview_image>
    </media>
</theme>
XML_CONTENT;

        $appDirWriter->writeFile($relativePath . '/theme.xml', $xmlContent);
        $output->writeln('<info>generated </info><comment>' . $relativePath . '/theme.xml</comment>');
    }

    /**
     * @param OutputInterface $output
     * @param \Magento\Framework\Filesystem\Directory\WriteInterface $appDirWriter
     * @param string $relativePath
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function createViewXmlFile(OutputInterface $output, $appDirWriter, $relativePath)
    {
        $xmlContent = <<<XML_CONTENT
<?xml version="1.0"?>
<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/view.xsd">
</view>
XML_CONTENT;

        $appDirWriter->writeFile($relativePath . '/etc/view.xml', $xmlContent);
        $output->writeln('<info>generated </info><comment>' . $relativePath . '/etc/view.xml</comment>');
    }
}
