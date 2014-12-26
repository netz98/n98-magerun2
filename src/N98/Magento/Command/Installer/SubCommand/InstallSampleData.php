<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\OperatingSystem;
use Symfony\Component\Process\ProcessBuilder;

class InstallSampleData extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->input->getOption('noDownload')) {
            return false;
        }

        $installationFolder = $this->config->getString('installationFolder');
        chdir($installationFolder);

        $magentoPackage = $this->config->getObject('magentoPackage'); /* @var $magentoPackage \Composer\Package\MemoryPackage */
        $extra  = $magentoPackage->getExtra();
        if (!isset($extra['sample-data'])) {
            return;
        }

        $dialog = $this->getCommand()->getHelper('dialog');

        if ($this->input->getOption('installSampleData') !== null) {
            $installSampleData = $this->getCommand()->parseBoolOption($this->input->getOption('installSampleData'));
        } else {
            $installSampleData = $dialog->askConfirmation($this->output, '<question>Install sample data?</question> <comment>[y]</comment>: ');
        }

        if ($installSampleData) {
            // Composer config
            $this->addComposerRepository();

            // Composer require
            foreach ($extra['sample-data'] as $extraPackageName => $extraPackageVersion) {
                $this->composerRequirePackage($extraPackageName, $extraPackageVersion);
            }

            $this->updateComposer();

            $this->runSampleDataInstaller();
        }
    }

    protected function addComposerRepository()
    {
        /**
         * @TODO Move repo data to dist config.
         */
        $processBuilder = new ProcessBuilder(
            array(
                $this->config['composer_bin'],
                'config',
                'repositories.magento',
                'composer',
                'http://packages.magento.com'
            )
        );

        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);
        $process->run();
    }

    protected function updateComposer()
    {
        $processBuilder = new ProcessBuilder(
            array(
                $this->config['composer_bin'],
                'update'
            )
        );
        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);
        $process->run();
    }

    /**
     * @param $extraPackageName
     * @param $extraPackageVersion
     */
    protected function composerRequirePackage($extraPackageName, $extraPackageVersion)
    {
        $processBuilder = new ProcessBuilder(
            array(
                $this->config['composer_bin'],
                'require',
                $extraPackageName . ':' . $extraPackageVersion,
                '--no-update',
                '--dev'
            )
        );

        $process = $processBuilder->getProcess();
        $process->run();
    }

    protected function runSampleDataInstaller()
    {
        if (OperatingSystem::isWindows()) {
            $php = 'php';
        } else {
            $php = '/usr/bin/env php';
        }

        $installationArgs = $this->config->getArray('installation_args');

        $processBuilder = new ProcessBuilder(
            array(
                $php,
                'dev/tools/Magento/Tools/SampleData/install.php',
                '--admin_username=' . $installationArgs['admin_username']
            )
        );

        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);
        $process->start();
        $process->wait(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->output->write('<error>sample-data > ' . $buffer . '</error>');
            } else {
                $this->output->write('sample-data > ' . $buffer, false);
            }
        });
    }
}