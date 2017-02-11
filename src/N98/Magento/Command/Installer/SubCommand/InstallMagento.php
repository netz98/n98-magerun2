<?php

namespace N98\Magento\Command\Installer\SubCommand;

use Exception;
use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\Exec;
use N98\Util\OperatingSystem;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class InstallMagento extends AbstractSubCommand
{
    /**
     * @deprecated since since 1.3.1; Use constant from Exec-Utility instead
     * @see Exec::CODE_CLEAN_EXIT
     */
    const EXEC_STATUS_OK = 0;

    const MAGENTO_INSTALL_SCRIPT_PATH = 'bin/magento';

    /**
     * @var \Closure
     */
    protected $notEmptyCallback;

    /**
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $this->notEmptyCallback = function ($input) {
            if (empty($input)) {
                throw new \InvalidArgumentException('Please enter a value');
            }
            return $input;
        };

        $this->getCommand()->getApplication()->setAutoExit(false);

        $dialog = $this->getCommand()->getHelper('dialog');

        $defaults = $this->commandConfig['installation']['defaults'];

        $useDefaultConfigParams = $this->hasFlagOrOptionalBoolOption('useDefaultConfigParams');

        $sessionSave = $useDefaultConfigParams ? $defaults['session-save'] : $dialog->ask(
            $this->output,
            '<question>Please enter the session save:</question> <comment>[' .
            $defaults['session-save'] . ']</comment>: ',
            $defaults['session-save']
        );

        $adminFrontname = $useDefaultConfigParams ? $defaults['backend-frontname'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin/backend frontname:</question> <comment>[' .
            $defaults['backend-frontname'] . ']</comment> ',
            $this->notEmptyCallback,
            false,
            $defaults['backend-frontname']
        );

        $currency = $useDefaultConfigParams ? $defaults['currency'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the default currency code:</question> <comment>[' .
            $defaults['currency'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['currency']
        );

        $locale = $useDefaultConfigParams ? $defaults['locale'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the locale code:</question> <comment>[' . $defaults['locale'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['locale']
        );

        $timezone = $useDefaultConfigParams ? $defaults['timezone'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the timezone:</question> <comment>[' . $defaults['timezone'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['timezone']
        );

        $adminUsername = $useDefaultConfigParams ? $defaults['admin-user'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin username:</question> <comment>[' .
            $defaults['admin-user'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin-user']
        );

        $adminPassword = $useDefaultConfigParams ? $defaults['admin-password'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin password:</question> <comment>[' .
            $defaults['admin-password'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin-password']
        );

        $adminFirstname = $useDefaultConfigParams ? $defaults['admin-firstname'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin\'s firstname:</question> <comment>[' .
            $defaults['admin-firstname'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin-firstname']
        );

        $adminLastname = $useDefaultConfigParams ? $defaults['admin-lastname'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin\'s lastname:</question> <comment>[' .
            $defaults['admin-lastname'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin-lastname']
        );

        $adminEmail = $useDefaultConfigParams ? $defaults['admin-email'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin\'s email:</question> <comment>[' .
            $defaults['admin-email'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin-email']
        );

        $validateBaseUrl = function ($url) {
            if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)) {
                throw new \InvalidArgumentException('Please enter a valid URL');
            }
            if (parse_url($url, \PHP_URL_HOST) == 'localhost') {
                throw new \InvalidArgumentException(
                    'localhost cause problems! Please use 127.0.0.1 or another hostname'
                );
            }

            return $url;
        };

        $baseUrl = ($this->input->getOption('baseUrl') !== null)
            ? $this->input->getOption('baseUrl')
            : $dialog->askAndValidate(
                $this->output,
                '<question>Please enter the base url:</question> ',
                $validateBaseUrl,
                false
            );
        $baseUrl = rtrim($baseUrl, '/') . '/'; // normalize baseUrl

        /**
         * Correct session save (common mistake)
         */
        if ($sessionSave == 'file') {
            $sessionSave = 'files';
        }
        $this->_getDefaultSessionFolder($sessionSave);

        $argv = array(
            'language'          => $locale,
            'timezone'          => $timezone,
            'db-host'           => $this->_prepareDbHost(),
            'db-name'           => $this->config->getString('db_name'),
            'db-user'           => $this->config->getString('db_user'),
            'base-url'          => $baseUrl,
            'use-rewrites'      => 1,
            'use-secure'        => 0,
            'use-secure-admin'  => 1,
            'admin-user'        => $adminUsername,
            'admin-lastname'    => $adminLastname,
            'admin-firstname'   => $adminFirstname,
            'admin-email'       => $adminEmail,
            'admin-password'    => $adminPassword,
            'session-save'      => $sessionSave,
            'backend-frontname' => $adminFrontname,
            'currency'          => $currency,
        );

        $dbPass = $this->config->getString('db_pass');
        if (!empty($dbPass)) {
            $argv['db-password'] = $dbPass;
        }

        if ($useDefaultConfigParams) {
            if (isset($defaults['encryption-key']) && strlen($defaults['encryption-key']) > 0) {
                $argv['encryption-key'] = $defaults['encryption-key'];
            }
            if (strlen($defaults['use-secure']) > 0) {
                $argv['use-secure'] = $defaults['use-secure'];
                $argv['base-url-secure'] = str_replace('http://', 'https://', $baseUrl);
            }
            if (strlen($defaults['use-rewrites']) > 0) {
                $argv['use-rewrites'] = $defaults['use-rewrites'];
            }
        }

        $this->config->setArray('installation_args', $argv);

        $this->runInstallScriptCommand($this->output, $this->config->getString('installationFolder'), $argv);
    }

    /**
     * @deprecated since 1.3.1 (obsolete)
     * @return string
     * @throws Exception
     */
    protected function getInstallScriptPath()
    {
        $installerScript = $this->config->getString('installationFolder') . '/' . self::MAGENTO_INSTALL_SCRIPT_PATH;
        if (!file_exists($installerScript)) {
            throw new RuntimeException('Installation script was not found.', 1);
        }

        return $installerScript;
    }

    /**
     * @param $sessionSave
     */
    protected function _getDefaultSessionFolder($sessionSave)
    {
        /**
         * Try to create session folder
         */
        $defaultSessionFolder = $this->config->getString('installationFolder') . '/var/session';
        if ($sessionSave == 'files' && !is_dir($defaultSessionFolder)) {
            @mkdir($defaultSessionFolder);
        }
    }

    /**
     * @return string
     */
    protected function _prepareDbHost()
    {
        $dbHost = $this->config->getString('db_host');

        if ($this->config->getInt('db_port') != 3306) {
            $dbHost .= ':' . strval($this->config->getInt('db_port'));

            return $dbHost;
        }

        return $dbHost;
    }

    /**
     * Invoke Magento PHP install script bin/magento setup:install
     *
     * @param OutputInterface $output
     * @param string $installationFolder folder where magento is installed in, must exists setup script in
     * @param array $argv
     * @return void
     */
    private function runInstallScriptCommand(OutputInterface $output, $installationFolder, array $argv)
    {
        $installArgs = '';
        foreach ($argv as $argName => $argValue) {
            if (is_null($argValue)) {
                $installArgs .= '--' . $argName . ' ';
            } elseif (is_bool($argValue)) {
                $installArgs .= '--' . $argName . '=' . (int) $argValue . ' ';
            } else {
                $installArgs .= '--' . $argName . '=' . escapeshellarg($argValue) . ' ';
            }
        }

        $output->writeln('<info>Start installation process.</info>');

        $installCommand = sprintf(
            '%s -ddisplay_startup_errors=1 -ddisplay_errors=1 -derror_reporting=-1 -f %s -- setup:install %s',
            OperatingSystem::getPhpBinary(),
            escapeshellarg($installationFolder . '/' . self::MAGENTO_INSTALL_SCRIPT_PATH),
            $installArgs
        );

        $output->writeln('<comment>' . $installCommand . '</comment>');
        $installException = null;
        $installationOutput = null;
        $returnStatus = null;
        try {
            Exec::run($installCommand, $installationOutput, $returnStatus);
        } catch (Exception $installException) {
            /* fall-through intended */
        }

        if (isset($installException) || $returnStatus !== Exec::CODE_CLEAN_EXIT) {
            $this->getCommand()->getApplication()->setAutoExit(true);
            throw new RuntimeException(
                sprintf('Installation failed (Exit code %s). %s', $returnStatus, $installationOutput),
                1,
                $installException
            );
        }
        $output->writeln('<info>Successfully installed Magento</info>');
        $encryptionKey = trim(substr(strstr($installationOutput, ':'), 1));
        $output->writeln('<comment>Encryption Key:</comment> <info>' . $encryptionKey . '</info>');
    }
}
