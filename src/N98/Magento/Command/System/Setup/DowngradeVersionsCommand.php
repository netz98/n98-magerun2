<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Api\ModuleInterface;
use N98\Magento\Api\ModuleListVersionIterator;
use N98\Magento\Api\ModuleVersion;
use Symfony\Component\Console\Command\Command;
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
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $this->output = $output;
        $this->dryRun = $input->getOption('dry-run');

        $status = Command::SUCCESS;

        foreach ($this->getModuleVersions() as $moduleVersion) {
            if ($this->processModule($output, $moduleVersion) !== Command::SUCCESS) {
                $status = Command::FAILURE;
            }
        }

        return $status;
    }

    /**
     * @param OutputInterface $output
     * @param ModuleVersion $module
     */
    private function processModule(OutputInterface $output, ModuleVersion $module)
    {
        try {
            // db version
            try {
                $dbVersion = $module->getDbVersion();
                if ($this->needsDowngrade($module, 'db', $dbVersion)) {
                    $this->dryRun || $module->setDbVersion($module->getVersion());
                }
            } catch (\BadMethodCallException $e) {
                // do not print anything
            }

            try {
                $dataVersion = $module->getDataVersion();

                if ($this->needsDowngrade($module, 'data', $dataVersion)) {
                    $this->dryRun || $module->setDataVersion($module->getVersion());
                }
            } catch (\BadMethodCallException $e) {
                // do not print anything
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param ModuleInterface $module
     * @param string $what
     * @param string $currentVersion
     *
     * @return bool
     */
    private function needsDowngrade(ModuleInterface $module, $what, $currentVersion): bool
    {
        $targetVersion = $module->getVersion();
        $needsDowngrade = 1 === \version_compare($currentVersion, $targetVersion);

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
