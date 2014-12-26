<?php

namespace N98\Magento\Command\Installer\SubCommand;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use N98\Magento\Command\SubCommand\AbstractSubCommand;

class InstallComposerPackages extends AbstractSubCommand
{
    /**
     * Check PHP environment agains minimal required settings modules
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function execute()
    {
        $this->output->writeln('<comment>Install composer packages</comment>');
        $processBuilder = new ProcessBuilder(
            array(
                $this->config['composer_bin'],
                'install'
            )
        );
        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);

        $process->start();
        $process->wait(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->output->write('<error>composer-error > ' . $buffer . '</error>');
            } else {
                $this->output->write('composer > ' . $buffer, false);
            }
        });

        return true;
    }
}