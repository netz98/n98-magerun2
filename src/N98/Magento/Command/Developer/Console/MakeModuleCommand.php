<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\App\Cache;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Status;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
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
        list($vendorName, $moduleName) = explode('_', $input->getArgument('modulename'));

        if (empty($vendorName) || empty($moduleName)) {
            throw new \InvalidArgumentException('Invalid module name. (Format Acme_Foo)');
        }

        $vendorName = ucfirst($vendorName);
        $moduleName = ucfirst($moduleName);

        $newModuleName = $vendorName . '_' . $moduleName;

        $filesystem = $this->get(Filesystem::class);
        /** @var $filesystem Filesystem */
        $appDirectoryWriter = $filesystem->getDirectoryWrite(DirectoryList::APP);

        $registrationFileBody = <<<FILE_BODY
<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    '${newModuleName}',
    __DIR__
);

FILE_BODY;
        $appDirectoryWriter->writeFile('code/' . $vendorName . '/' . $moduleName . '/registration.php', $registrationFileBody);

        $moduleFileBody = <<<FILE_BODY
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="$newModuleName" setup_version="1.0.0">
        <sequence>
        </sequence>
    </module>
</config>

FILE_BODY;

        $appDirectoryWriter->writeFile('code/' . $vendorName . '/' . $moduleName . '/etc/module.xml', $moduleFileBody);

        $output->writeln('<info>create new module </info><comment>' . $newModuleName . '</comment>');


        $appDirectoryReader = $filesystem->getDirectoryRead(DirectoryList::APP);
        $moduleRegistrationFile = $appDirectoryReader->getAbsolutePath(
            'code/' . $vendorName . '/' . $moduleName . '/registration.php'
        );
        include($moduleRegistrationFile);

        $applicationCache = $this->get(Cache::class);
        $applicationCache->clean();

        $cleanupFiles = $this->get(CleanupFiles::class);
        $cleanupFiles->clearCodeGeneratedClasses();

        $moduleStatus = $this->get(Status::class);
        /** @var $moduleStatus Status */
        $moduleStatus->setIsEnabled(true, [$newModuleName]);

        $this->changeToNewModule($output, $newModuleName);
    }

    /**
     * @param OutputInterface $output
     * @param string $newModuleName
     * @return int
     */
    protected function changeToNewModule(OutputInterface $output, $newModuleName)
    {
        $command = $this->getApplication()->find('module');
        $arguments = [
            'module' => $newModuleName
        ];
        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

}
