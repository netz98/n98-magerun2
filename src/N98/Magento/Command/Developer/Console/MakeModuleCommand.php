<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\App\Cache;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Status;
use N98\Magento\Command\Developer\Console\Structure\ModuleNameStructure;
use N98\Util\ComposerLock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class MakeModuleCommand
 * @package N98\Magento\Command\Developer\Console
 */
class MakeModuleCommand extends AbstractGeneratorCommand
{
    /**
     * Directory where module should be created in
     *
     * @var string
     */
    private $modulesBaseDir;
    /**
     *
     * @var ComposerLock
     */
    private $magentoComposerLock;

    protected function configure()
    {
        $this
            ->setName('make:module')
            ->addArgument('modulename', InputArgument::OPTIONAL, 'Module name (Vendor_Module)')
            ->addOption(
                'modules-base-dir',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Directory where module should be created. Default is app/code if not reconfigured'
            )
            ->setDescription('Creates a new module');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('modulename')) {
            $helper = $this->getHelper('question');
            $question = new Question('Module name: ');
            $question->setValidator(function ($value) {
                try {
                    new ModuleNameStructure($value);
                } catch (\InvalidArgumentException $e) {
                    throw new \RuntimeException($e->getMessage());
                }
                return $value;
            });
            $question->setMaxAttempts(null);
            $moduleName = $helper->ask($input, $output, $question);
            $input->setArgument('modulename', $moduleName);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->magentoComposerLock = new ComposerLock(
            $this->getMagerunApplication()->getMagentoRootFolder()
        );

        $moduleName = new ModuleNameStructure($input->getArgument('modulename'));

        $this->modulesBaseDir = $input->getOption('modules-base-dir');

        if (empty($this->modulesBaseDir)) {
            $magerunConfig = $this->getMagerunApplication()->getConfig();
            $this->modulesBaseDir = $magerunConfig['commands'][__CLASS__]['defaultModulesBaseDir'];
        }

        $filesystem = $this->get(Filesystem::class);
        /** @var $filesystem Filesystem */
        $rootDirectoryWriter = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $appDirectoryReader = $filesystem->getDirectoryRead(DirectoryList::ROOT);

        $moduleList = $this->create(ModuleListInterface::class);
        /** @var $moduleList ModuleListInterface */

        $detectedModule = $moduleList->getOne($moduleName->getFullModuleName());
        if ($detectedModule !== null) {
            // module already exist!
            $output->writeln('<warning>Module already exist. Skip creation.</warning>');
            return $this->changeToNewModule($output, $moduleName);
        }

        $this->createRegistrationFile($moduleName, $rootDirectoryWriter);
        $this->createComposerFile($moduleName, $rootDirectoryWriter);
        $this->createEtcModuleFile($moduleName, $rootDirectoryWriter);
        $this->createTestDirectories($moduleName, $rootDirectoryWriter);
        $this->includeRegistrationFile($moduleName, $appDirectoryReader);

        $output->writeln(
            '<info>created new module </info><comment>' . $moduleName->getFullModuleName() . '</comment>'
            . '<info> in directory </info><comment>' . $this->modulesBaseDir . '</comment>'
        );

        $this->activateNewModuleInSystem($output, $moduleName);
        $this->cleanClassCache();

        $this->changeToNewModule($output, $moduleName);

        return Command::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param ModuleNameStructure $moduleName
     * @return int
     * @throws \Exception
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
     * @throws \Magento\Framework\Exception\FileSystemException
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
            $this->modulesBaseDir . '/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/registration.php',
            $registrationFileBody
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param WriteInterface $appDirectoryWriter
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function createComposerFile(ModuleNameStructure $moduleName, WriteInterface $appDirectoryWriter)
    {
        if ($this->getMagerunApplication()->isMageOs()) {
            $frameworkPackageName = 'mage-os/framework';
        } else {
            $frameworkPackageName = 'magento/framework';
        }

        $frameworkPackage = $this->magentoComposerLock->getPackageByName($frameworkPackageName);

        $composerFileBody = $this->getHelper('twig')->render(
            'dev/console/make/module/composer.json.twig',
            [
                'vendor'    => $moduleName->getVendorName(),
                'module'    => $moduleName->getShortModuleName(),
                'php_version' => '~' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.0',
                'magento_framework_package' => $frameworkPackageName,
                'magento_framework_version' => '~' . $frameworkPackage->version ?? '*',
                'namespace' => str_replace('\\', '\\\\', $this->getModuleNamespace($moduleName->getFullModuleName())),
            ]
        );

        $appDirectoryWriter->writeFile(
            $this->modulesBaseDir . '/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/composer.json',
            $composerFileBody
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param WriteInterface $appDirectoryWriter
     * @throws \Magento\Framework\Exception\FileSystemException
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
            $this->modulesBaseDir . '/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/etc/module.xml',
            $moduleFileBody
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param WriteInterface $appDirectoryWriter
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function createTestDirectories(ModuleNameStructure $moduleName, WriteInterface $appDirectoryWriter)
    {
        $appDirectoryWriter->create(
            $this->modulesBaseDir . '/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/Test/Unit'
        );
    }

    /**
     * @param ModuleNameStructure $moduleName
     * @param ReadInterface $appDirectoryReader
     */
    private function includeRegistrationFile(ModuleNameStructure $moduleName, ReadInterface $appDirectoryReader)
    {
        $moduleRegistrationFile = $appDirectoryReader->getAbsolutePath(
            $this->modulesBaseDir . '/' . $moduleName->getVendorName() . '/' . $moduleName->getShortModuleName() . '/registration.php'
        );

        include $moduleRegistrationFile;
    }

    /**
     * @return void
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
