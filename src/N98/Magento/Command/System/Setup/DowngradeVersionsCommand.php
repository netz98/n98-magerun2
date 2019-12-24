<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Api\ModuleInterface;
use N98\Magento\Api\ModuleListVersionIterator;
use N98\Magento\Api\ModuleVersion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DowngradeVersionsCommand
 * @package N98\Magento\Command\System\Setup
 */
class DowngradeVersionsCommand extends AbstractSetupCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * Setup
     */
    protected function configure()
    {
        $this
            ->setName('sys:setup:downgrade-versions')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Write what to change but do not do any changes')
            ->setDescription('Automatically downgrade schema and module versions');
        $help
            = <<<HELP
If version numbers are too high - normally happens while developing - this command will lower them to the expected ones.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if (!$this->initMagento()) {
            return;
        }

        $this->output = $output;
        $this->dryRun = $input->getOption('dry-run');

        foreach ($this->getModuleVersions() as $moduleVersion) {
            $this->processModule($moduleVersion);
        }
    }

    /**
     * @param $module
     */
    private function processModule(ModuleVersion $module)
    {
        $dryRun = $this->dryRun;

        // data version
        if ($this->needsDowngrade($module, 'data', $module->getDataVersion())) {
            $dryRun || $module->setDataVersion($module->getVersion());
        }

        // db version
        if ($this->needsDowngrade($module, 'db', $module->getDbVersion())) {
            $dryRun || $module->setDbVersion($module->getVersion());
        }
    }

    /**
     * @param ModuleInterface $module
     * @param string $what
     * @param string $currentVersion
     *
     * @return bool
     */
    private function needsDowngrade(ModuleInterface $module, $what, $currentVersion)
    {
        $targetVersion = $module->getVersion();
        $needsDowngrade = 1 === version_compare($currentVersion, $targetVersion);

        if (!$needsDowngrade) {
            return false;
        }

        $this->output->writeln(
            sprintf(
                "<info>Change module '%s' %s-version from %s to %s.</info>",
                $module->getName(),
                $what,
                $currentVersion,
                $targetVersion
            )
        );

        return true;
    }

    /**
     * @return ModuleListVersionIterator
     */
    private function getModuleVersions()
    {
        return new ModuleListVersionIterator($this->moduleList, $this->resource);
    }
}
