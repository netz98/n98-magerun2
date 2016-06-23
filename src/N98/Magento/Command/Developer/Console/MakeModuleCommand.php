<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\App\Cache;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\Status;
use N98\Magento\Command\Developer\Console\Structure\ModuleNameStructure;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModuleCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:module')
            ->addArgument('modulename', InputArgument::REQUIRED)
            ->setDescription('Creates a new module');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleName = new ModuleNameStructure($input->getArgument('modulename'));
        
        $filesystem = $this->get(Filesystem::class);
        /** @var $filesystem Filesystem */
        $appDirectoryWriter = $filesystem->getDirectoryWrite(DirectoryList::APP);
        $appDirectoryReader = $filesystem->getDirectoryRead(DirectoryList::APP);

        $this->createRegistrationFile($moduleName, $appDirectoryWriter);
        $this->createComposerFile($moduleName, $appDirectoryWriter);
        $this->createEtcModuleFile($moduleName, $appDirectoryWriter);
        $this->createTestDirectories($moduleName, $appDirectoryWriter);
        $this->includeRegistrationFile($moduleName, $appDirectoryReader);

        $output->writeln('<info>created new module </info><comment>' . $moduleName->getFullModuleName() . '</comment>');

        $this->activateNewModuleInSystem($output, $moduleName);
        $this->cleanClassCache();

        $this->changeToNewModule($output, $moduleName);
    }

    /**
     * @param OutputInterface $output
     * @param ModuleNameStructure $moduleName
     * @return int
     */
    private function changeToNewModule(OutputInterface $output, ModuleNameStructure $moduleName)
    {
        $command = $this->getApplication()->find('module');
        $arguments = [
            'module' => $moduleName->getFullModuleName(),
        ];
        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param WriteInterface $appDirectoryWriter
     */
    private function createRegistrationFile(ModuleNameStructure $moduleName, WriteInterface $appDirectoryWriter)
    {
        $registrationFileBody = <<<FILE_BODY
<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    '{$moduleName->getFullModuleName()}',
    __DIR__
);

FILE_BODY;
        $appDirectoryWriter->writeFile(
            'code/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/registration.php',
            $registrationFileBody
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param WriteInterface $appDirectoryWriter
     */
    private function createComposerFile(ModuleNameStructure $moduleName, WriteInterface $appDirectoryWriter)
    {
        $composerFileBody = $this->getHelper('twig')->render(
            'dev/console/make/module/composer.json.twig',
            [
                'vendor' => $moduleName->getVendorName(),
                'module' => $moduleName->getShortModuleName(),
                'namespace' => str_replace('\\', '\\\\', $this->getModuleNamespace($moduleName->getFullModuleName())),
            ]
        );
        var_dump($composerFileBody);

        $appDirectoryWriter->writeFile(
            'code/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/composer.json',
            $composerFileBody
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param WriteInterface $appDirectoryWriter
     */
    private function createEtcModuleFile(ModuleNameStructure $moduleName, WriteInterface $appDirectoryWriter)
    {
        $moduleFileBody = <<<FILE_BODY
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="{$moduleName->getFullModuleName()}" setup_version="1.0.0">
        <sequence>
        </sequence>
    </module>
</config>

FILE_BODY;

        $appDirectoryWriter->writeFile(
            'code/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/etc/module.xml', $moduleFileBody
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param WriteInterface $appDirectoryWriter
     */
    private function createTestDirectories(ModuleNameStructure $moduleName, WriteInterface $appDirectoryWriter)
    {
        $appDirectoryWriter->create(
            'code/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/Test/Unit'
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param ReadInterface $appDirectoryReader
     */
    private function includeRegistrationFile(ModuleNameStructure $moduleName, ReadInterface $appDirectoryReader)
    {
        $moduleRegistrationFile = $appDirectoryReader->getAbsolutePath(
            'code/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/registration.php'
        );

        include($moduleRegistrationFile);
    }

    /**
     * @return voic
     */
    private function cleanClassCache()
    {
        $applicationCache = $this->get(Cache::class);
        $applicationCache->clean();

        $cleanupFiles = $this->get(CleanupFiles::class);
        $cleanupFiles->clearCodeGeneratedClasses();
    }

    /**
     * @param OutputInterface $output
     * @param ModuleNameStructure $moduleName
     */
    private function activateNewModuleInSystem(OutputInterface $output, ModuleNameStructure $moduleName)
    {
        $moduleStatus = $this->get(Status::class);
        /** @var $moduleStatus Status */
        $moduleStatus->setIsEnabled(true, [$moduleName->getFullModuleName()]);

        $output->writeln('<info>activated new module </info><comment>' . $moduleName->getFullModuleName() . '</comment>');
    }
}
