<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\Console\Helper\ComposerHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class DownloadMagento extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->input->getOption('noDownload')) {
            return false;
        }

        try {
            $this->checkMagentoConnectCredentials($this->output);

            $package = $this->config['magentoVersionData'];
            $this->config->setArray('magentoPackage', $package);

            if (file_exists($this->config->getString('installationFolder') . DIRECTORY_SEPARATOR . 'app/etc/env.php')) {
                $this->output->writeln('<error>A magento installation already exists in this folder </error>');
                return false;
            }

            $args = [
                $this->config['composer_bin'],
                'create-project',
            ];

            // Add composer options
            foreach ($package['options'] as $optionName => $optionValue) {
                $args[] = '--' . $optionName . ($optionValue === true ? '' : '=' . $optionValue);
            }

            // Add arguments
            $args[] = $package['package'];
            $args[] = $this->config->getString('installationFolder');
            $args[] = $package['version'];

            if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                $args[] = '-vvv';
            }

            /**
             * @TODO use composer helper
             */
            $processBuilder = new ProcessBuilder($args);

            $process = $processBuilder->getProcess();
            $process->setInput($this->input);
            if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                $this->output->writeln($process->getCommandLine());
            }

            $process->setTimeout(86400);
            $process->start();
            $process->wait(function ($type, $buffer) {
                $this->output->write($buffer, false, OutputInterface::OUTPUT_RAW);
            });
        } catch (\Exception $e) {
            $this->output->writeln('<error>' . $e->getMessage() . '</error>');
            return false;
        }

        return true;
    }

    /**
     * construct a folder to where magerun will download the source to,
     * cache git/hg repositories under COMPOSER_HOME
     *
     * @param $composer
     * @param $package
     * @param $installationFolder
     *
     * @return string
     */
    protected function getTargetFolderByType($composer, $package, $installationFolder)
    {
        $type = $package->getSourceType();
        if ($this->getCommand()->isSourceTypeRepository($type)) {
            $targetPath = sprintf(
                '%s/%s/%s/%s',
                $composer->getConfig()->get('cache-dir'),
                '_n98_magerun_download',
                $type,
                preg_replace('{[^a-z0-9.]}i', '-', $package->getSourceUrl())
            );
        } else {
            $targetPath = sprintf(
                '%s/%s',
                $installationFolder,
                '_n98_magerun_download'
            );
        }

        return $targetPath;
    }

    /**
     * @param OutputInterface $output
     */
    protected function checkMagentoConnectCredentials(OutputInterface $output)
    {
        $configKey = 'http-basic.repo.magento.com';

        $composerHelper = $this->getCommand()->getHelper('composer');
        /** @var $composerHelper ComposerHelper */
        $authConfig = $composerHelper->getConfigValue($configKey);

        if (!isset($authConfig->username)
            || !isset($authConfig->password)
        ) {
            $this->output->writeln(array(
                '',
                $this->getCommand()
                    ->getHelperSet()
                    ->get('formatter')
                    ->formatBlock('Authentication', 'bg=blue;fg=white', true),
                '',
            ));

            $this->output->writeln(array(
                'You need to create a secury key. Login at magentocommerce.com.',
                'Developers -> Secure Keys. <info>Use public key as username and private key as password</info>',
                ''
            ));
            $dialog = $this->getCommand()->getHelper('dialog');

            $username = $dialog->askAndValidate(
                $output,
                '<comment>Please enter your public key: </comment>',
                function ($value) {
                    if ('' === trim($value)) {
                        throw new \Exception('The private key (auth token) can not be empty');
                    }

                    return $value;
                },
                20,
                false
            );


            $password = $dialog->askHiddenResponseAndValidate(
                $output,
                '<comment>Please enter your private key: </comment>',
                function ($value) {
                    if ('' === trim($value)) {
                        throw new \Exception('The private key (auth token) can not be empty');
                    }

                    return $value;
                },
                20,
                false
            );

            $composerHelper->setConfigValue($configKey, [$username, $password]);
        }
    }
}
