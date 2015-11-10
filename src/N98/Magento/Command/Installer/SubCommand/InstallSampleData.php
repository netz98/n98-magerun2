<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\OperatingSystem;
use Symfony\Component\Process\Process;
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
            $this->changeComposerMiniumStability();
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

    protected function changeComposerMiniumStability()
    {
        $composerJsonFile = $this->config->getString('installationFolder') . DIRECTORY_SEPARATOR . 'composer.json';
        // @TODO Find a better solution instead of self-parsing composer.json file.
        $jsonConfig = \json_decode(\file_get_contents($composerJsonFile));

        if (isset($jsonConfig->{'minimum-stability'}) && $jsonConfig->{'minimum-stability'} == 'dev') {
            return;
        }

        $jsonConfig->{'minimum-stability'} = 'dev';
        \file_put_contents($composerJsonFile, \json_encode($jsonConfig, \JSON_PRETTY_PRINT));

        $this->output->writeln('<info>Changed <comment>minimum-stability</comment> in composer.json to <comment>dev</comment></info>');
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
    protected function  composerRequirePackage($extraPackageName, $extraPackageVersion)
    {
        $processBuilder = new ProcessBuilder(
            array(
                $this->config['composer_bin'],
                'require',
                $extraPackageName . ':' . $extraPackageVersion,
                '--dev'
            )
        );

        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);
        $process->start();
        $process->wait(function ($type, $buffer) {
            $this->output->write('sample-data > ' . $buffer, false);
        });
    }

    protected function runSampleDataInstaller()
    {
        $installationArgs = $this->config->getArray('installation_args');

        $processBuilder = new ProcessBuilder(
            array(
                'php',
                'bin/magento',
                'sampledata:install',
                $installationArgs['admin-user']
            )
        );

        if (!OperatingSystem::isWindows()) {
            $processBuilder->setPrefix('/usr/bin/env');
        }

        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);
        $process->start();
        $process->wait(function ($type, $buffer) {
            $this->output->write($buffer, false);
        });


        // @TODO Refactor code duplication
        if (!OperatingSystem::isWindows()) {
            $processBuilder->setPrefix('/usr/bin/env');
        }

        $processBuilder = new ProcessBuilder(
            array(
                'php',
                'bin/magento',
                'setup:upgrade'
            )
        );
        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);
        $process->start();
        $process->wait(function ($type, $buffer) {
            $this->output->write($buffer, false);
        });
    }
}
