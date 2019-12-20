<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use Symfony\Component\Process\ProcessBuilder;

class InstallComposerPackages extends AbstractSubCommand
{
    /**
     * Check PHP environment agains minimal required settings modules
     *
     * @return void
     *
     * @throws \Exception
     */
    public function execute()
    {
        $this->output->writeln('<comment>Install composer packages</comment>');
        $processBuilder = new ProcessBuilder(array_merge($this->config['composer_bin'], ['install']));
        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);

        $process->start();
        $process->wait(function ($type, $buffer) {
            $this->output->write('composer > ' . $buffer, false);
        });
    }
}
