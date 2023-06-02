<?php

namespace N98\Magento\Command\Developer\Module;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Developer\Module
 */
class ListMissingFromConfigCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @param \Magento\Framework\App\DeploymentConfig\Reader $deploymentConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @return void
     */
    public function inject(
        \Magento\Framework\App\DeploymentConfig\Reader $deploymentConfig,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->fileSystem = $filesystem;
    }

    protected function configure()
    {
        $this
            ->setName('dev:module:list-missing-declaration')
            ->setDescription('List all modules that are not in app/etc/config.php')
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Exclude module from being scanned',
                []
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        $this->initMagento();

        $modules = $this->deploymentConfig->load()['modules'];

        // the below constants are defined in magentos app/autoload.php
        $vendorPath = BP . trim(require VENDOR_PATH, '.');
        $appDir = $this->fileSystem->getDirectoryRead('app')->getAbsolutePath() . 'code';

        $finder = Finder::create()
            ->files()
            ->followLinks()
            ->ignoreUnreadableDirs(true)
            ->ignoreVCS(true)
            ->name('module.xml')
            ->in([$appDir, $vendorPath]);

        $excludeList = $input->getOption('exclude');

        $moduleNames = [];
        foreach ($finder as $file) {
            $xml = simplexml_load_file($file);
            $schemaData = json_decode(json_encode((array)$xml), true);
            if (!isset($schemaData['module']['@attributes']['name'])) {
                continue;
            }
            $moduleName = $schemaData['module']['@attributes']['name'];
            if (!strlen($moduleName)) {
                continue;
            }
            if (stripos($moduleName, 'Magento_Test') !== false) {
                continue;  // Skip test modules from magento
            }
            if (in_array($moduleName, ['Magento_A', 'Magento_B', 'Magento_FirstModule'])) {
                continue;
            }
            if (in_array($moduleName, $excludeList)) {
                continue;
            }
            $moduleNames[] = $moduleName;
        }

        $missingModules = [];
        foreach ($moduleNames as $moduleName) {
            if (!isset($modules[$moduleName])) {
                $missingModules[] = $moduleName;
            }
        }

        if (empty($missingModules)) {
            $output->writeln("<info>Done</info>");
            return Command::SUCCESS;
        }
        $output->writeln("<error>The following modules are missing from deployment config</error>");
        $output->writeln(implode(PHP_EOL, $missingModules));

        return Command::FAILURE;
    }
}
