<?php

namespace N98\Magento\Command\Installer\SubCommand;

use Exception;
use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\Console\Helper\ComposerHelper;
use N98\Util\Exec;
use N98\Util\ProcessArguments;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

/**
 * Class DownloadMagento
 * @package N98\Magento\Command\Installer\SubCommand
 */
class DownloadMagento extends AbstractSubCommand
{
    /**
     * @throws Exception
     * @return void
     */
    public function execute()
    {
        if ($this->input->getOption('noDownload')) {
            return;
        }

        try {
            $this->implementation();
        } catch (Exception $e) {
            throw new RuntimeException('Error while downloading magento, aborting install', 0, $e);
        }
    }

    private function implementation()
    {
        $this->checkMagentoConnectCredentials($this->input, $this->output);

        $package = $this->config['magentoVersionData'];
        $this->config->setArray('magentoPackage', $package);

        if (file_exists($this->config->getString('installationFolder') . '/' . $this->getConfigDir() . '/env.php')) {
            throw new RuntimeException('A magento installation already exists in this folder');
        }

        $this->composerCreateProject($package);

        if ($this->config['composer_major_version'] === 2) {
            $this->composerAllowPlugins('dealerdirect/phpcodesniffer-composer-installer');
            $this->composerAllowPlugins('laminas/laminas-dependency-plugin');
            $this->composerAllowPlugins('magento/*');
        }

        $this->composerInstall();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function checkMagentoConnectCredentials(InputInterface $input, OutputInterface $output)
    {
        $configKey = 'http-basic.repo.magento.com';

        $composerHelper = $this->getCommand()->getHelper('composer');

        /** @var $composerHelper ComposerHelper */
        $authConfig = $composerHelper->getConfigValue($configKey);

        if (!isset($authConfig->username) || !isset($authConfig->password)) {
            $this->output->writeln([
                '',
                $this->getCommand()
                    ->getHelperSet()
                    ->get('formatter')
                    ->formatBlock('Authentication', 'bg=blue;fg=white', true),
                '',
            ]);

            $this->output->writeln([
                'You need to create a security key. Login at https://marketplace.magento.com/customer/accessKeys/.',
                'My Profile -> Access Keys. <info>Use public key as username and private key as password</info>',
                '',
            ]);
            $questionHelper = $this->getCommand()->getHelper('question');

            $question = new Question('<question>Please enter your public key: </question>');
            $question->setValidator(function ($value) {
                if ('' === $value) {
                    throw new Exception('The public key (auth token) can not be empty');
                }

                return $value;
            });
            $question->setMaxAttempts(20);
            $question->setHidden(false);

            $username = $questionHelper->ask($input, $output, $question);

            $question = new Question('<question>Please enter your private key: </question>');
            $question->setMaxAttempts(20);
            $question->setHidden(true);
            $question->setValidator(function ($value) {
                if ('' === $value) {
                    throw new Exception('The private key (auth token) can not be empty');
                }

                return $value;
            });

            $password = $questionHelper->ask(
                $input,
                $output,
                $question
            );

            $composerHelper->setConfigValue($configKey, [$username, $password]);
        }
    }

    /**
     * This method emulates the behavior of the `Magento\Framework\App\Filesystem\DirectoryList` component which, in
     * the end, reads the config directory path from the `$_SERVER['MAGE_DIR']['etc']['path']` if it exists and falls
     * back on the `app/etc` default value otherwise. Obviously is not possible to use the `DirectoryList` component
     * here because Magento has not been downloaded yet; so we have to emulate the original behavior.
     *
     * @return string
     */
    private function getConfigDir()
    {
        if (isset($_SERVER['MAGE_DIRS']['etc']['path'])) {
            return trim($_SERVER['MAGE_DIRS']['etc']['path'], DIRECTORY_SEPARATOR);
        }
        return 'app/etc';
    }

    /**
     * @param $package
     * @return void
     */
    private function composerCreateProject($package): void
    {
        $args = new ProcessArguments(array_merge($this->config['composer_bin'], ['create-project']));
        $args
            // Add composer options
            ->addArgs($package['options'])
            ->addArg('--no-dev')
            ->addArg('--no-install')
            // Add arguments
            ->addArg($package['package'])
            ->addArg($this->config->getString('installationFolder'))
            ->addArg($package['version']);

        if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
            $args->addArg('-vvv');
        }

        $process = $args->createProcess();
        if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
            $this->output->writeln($process->getCommandLine());
        }

        $process->setTimeout(86400);
        $process->start();
        $code = $process->wait(function ($type, $buffer) {
            $this->output->write($buffer, false, OutputInterface::OUTPUT_RAW);
        });

        if (Exec::CODE_CLEAN_EXIT !== $code) {
            throw new RuntimeException(
                'Non-zero exit code for composer create-project command: ' . $process->getCommandLine()
            );
        }
    }

    /**
     * @param string $pluginName
     * @return void
     */
    protected function composerAllowPlugins($pluginName): void
    {
        $process = new Process(
            array_merge(
                $this->config['composer_bin'],
                [
                    'config',
                    'allow-plugins.' . $pluginName,
                    'true'
                ]
            )
        );

        $process->setTimeout(86400);
        $process->start();
        $process->wait(function ($type, $buffer) {
            $this->output->write('composer > ' . $buffer, false);
        });
    }

    /**
     * @return void
     */
    protected function composerInstall(): void
    {
        $process = new Process(array_merge($this->config['composer_bin'], ['install']));
        $process->setTimeout(86400);
        $process->start();
        $process->wait(function ($type, $buffer) {
            $this->output->write('composer > ' . $buffer, false);
        });
    }
}
