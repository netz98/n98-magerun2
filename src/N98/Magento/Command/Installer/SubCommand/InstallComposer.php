<?php

namespace N98\Magento\Command\Installer\SubCommand;

use Composer\IO\ConsoleIO;
use Composer\Util\RemoteFilesystem;
use N98\Util\OperatingSystem;
use N98\Magento\Command\SubCommand\AbstractSubCommand;

class InstallComposer extends AbstractSubCommand
{
    /**
     * @var int
     */
    const EXEC_STATUS_OK = 0;

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function execute()
    {
        if (OperatingSystem::isProgramInstalled('composer.phar')) {
            $composerBin = 'composer.phar';
        } elseif (OperatingSystem::isProgramInstalled('composer')) {
            $composerBin = 'composer';
        } elseif (OperatingSystem::isProgramInstalled('composer.bat')) {
            $composerBin = 'composer';
        }

        if (empty($composerBin)) {
            $composerBin = $this->downloadComposer();
        }

        if (empty($composerBin)) {
            throw new \Exception('Cannot find or install composer. Please try it manually. https://getcomposer.org/');
        }

        $this->output->writeln('<info>Found executable <comment>' . $composerBin . '</comment></info>');
        $this->config['composer_bin'] = $composerBin;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function downloadComposer()
    {
        $this->output->writeln('<info>Could not find composer. Try to download it.</info>');
        $io = new ConsoleIO($this->input, $this->output, $this->getCommand()->getHelperSet());
        $rfs = new RemoteFilesystem($io);
        $composerInstaller = $rfs->getContents('getcomposer.org', 'https://getcomposer.org/installer', true);

        $tempComposerInstaller = $this->config['installationFolder'] . '/_composer_installer.php';
        file_put_contents($tempComposerInstaller, $composerInstaller);

        if (OperatingSystem::isWindows()) {
            $installCommand = 'php ' . $tempComposerInstaller . ' --force';
        } else {
            $installCommand = '/usr/bin/env php ' . $tempComposerInstaller . ' --force';
        }

        $this->output->writeln('<comment>' . $installCommand . '</comment>');
        exec($installCommand, $installationOutput, $returnStatus);
        unlink($tempComposerInstaller);
        $installationOutput = implode(PHP_EOL, $installationOutput);
        if ($returnStatus !== self::EXEC_STATUS_OK) {
            throw new \Exception('Installation failed.' . $installationOutput);
        } else {
            $this->output->writeln('<info>Successfully installed composer to Magento root</info>');
        }

        return $this->config['installationFolder'] . '/composer.phar';
    }
}
