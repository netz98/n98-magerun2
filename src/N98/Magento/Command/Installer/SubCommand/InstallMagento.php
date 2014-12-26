<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Util\OperatingSystem;
use N98\Magento\Command\SubCommand\AbstractSubCommand;

class InstallMagento extends AbstractSubCommand
{
    /**
     * @type int
     */
    const EXEC_STATUS_OK = 0;

    /**
     * @var \Closure
     */
    protected $notEmptyCallback;

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $this->notEmptyCallback = function($input) {
            if (empty($input)) {
                throw new \InvalidArgumentException('Please enter a value');
            }
            return $input;
        };

        $this->getCommand()->getApplication()->setAutoExit(false);

        $dialog = $this->getCommand()->getHelper('dialog');

        $defaults = $this->commandConfig['installation']['defaults'];

        $useDefaultConfigParams = $this->getCommand()->parseBoolOption($this->input->getOption('useDefaultConfigParams'));

        $sessionSave = $useDefaultConfigParams ? $defaults['session_save'] : $dialog->ask(
            $this->output,
            '<question>Please enter the session save:</question> <comment>[' . $defaults['session_save'] . ']</comment>: ',
            $defaults['session_save']
        );

        $adminFrontname = $useDefaultConfigParams ? $defaults['admin_frontname'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin frontname:</question> <comment>[' . $defaults['admin_frontname'] . ']</comment> ',
            $this->notEmptyCallback,
            false,
            $defaults['admin_frontname']
        );

        $currency = $useDefaultConfigParams ? $defaults['currency'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the default currency code:</question> <comment>[' . $defaults['currency'] . ']</comment>: ',
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

        $adminUsername = $useDefaultConfigParams ? $defaults['admin_username'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin username:</question> <comment>[' . $defaults['admin_username'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin_username']
        );

        $adminPassword = $useDefaultConfigParams ? $defaults['admin_password'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin password:</question> <comment>[' . $defaults['admin_password'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin_password']
        );

        $adminFirstname = $useDefaultConfigParams ? $defaults['admin_firstname'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin\'s firstname:</question> <comment>[' . $defaults['admin_firstname'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin_firstname']
        );

        $adminLastname = $useDefaultConfigParams ? $defaults['admin_lastname'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin\'s lastname:</question> <comment>[' . $defaults['admin_lastname'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin_lastname']
        );

        $adminEmail = $useDefaultConfigParams ? $defaults['admin_email'] : $dialog->askAndValidate(
            $this->output,
            '<question>Please enter the admin\'s email:</question> <comment>[' . $defaults['admin_email'] . ']</comment>: ',
            $this->notEmptyCallback,
            false,
            $defaults['admin_email']
        );

        $validateBaseUrl = function($url) {
            if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)) {
                throw new \InvalidArgumentException('Please enter a valid URL');
            }
            if (parse_url($url, \PHP_URL_HOST) ==  'localhost') {
                throw new \InvalidArgumentException('localhost cause problems! Please use 127.0.0.1 or another hostname');
            }

            return $url;
        };

        $baseUrl = ($this->input->getOption('baseUrl') !== null) ? $this->input->getOption('baseUrl') : $dialog->askAndValidate(
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
            'language'                   => $locale,
            'timezone'                   => $timezone,
            'db_host'                    => $this->_prepareDbHost(),
            'db_name'                    => $this->config->getString('db_name'),
            'db_user'                    => $this->config->getString('db_user'),
            'base_url'                   => $baseUrl,
            'use_rewrites'               => true,
            'use_secure'                 => false,
            'base_url_secure'            => '',
            'use_secure_admin'           => true,
            'admin_username'             => $adminUsername,
            'admin_lastname'             => $adminLastname,
            'admin_firstname'            => $adminFirstname,
            'admin_email'                => $adminEmail,
            'admin_password'             => $adminPassword,
            'session_save'               => $sessionSave,
            'backend_frontname'          => $adminFrontname,
            'currency'                   => $currency,
        );

        $dbPass = $this->config->getString('db_pass');
        if (!empty($dbPass)) {
            $argv['db_pass'] = $dbPass;
        }

        if ($useDefaultConfigParams) {
            if (strlen($defaults['encryption_key']) > 0) {
                $argv['encryption_key'] = $defaults['encryption_key'];
            }
            if (strlen($defaults['use_secure']) > 0) {
                $argv['use_secure'] = $defaults['use_secure'];
                $argv['base_url_secure'] = str_replace('http://', 'https://', $baseUrl);
            }
            if (strlen($defaults['use_rewrites']) > 0) {
                $argv['use_rewrites'] = $defaults['use_rewrites'];
            }
        }

        if (empty($argv['base_url_secure'])) {
            $argv['base_url_secure'] = $argv['base_url'];
        }

        $this->config->setArray('installation_args', $argv);

        $installArgs = '';
        foreach ($argv as $argName => $argValue) {
            if (is_null($argValue)) {
                $installArgs .= '--' . $argName . ' ';
            } elseif (is_bool($argValue)) {
                if ($argValue) {
                    $argValue = 'true';
                } else {
                    $argValue = 'false';
                }
                $installArgs .= '--' . $argName . '=' . $argValue . ' ';
            } else {
                $installArgs .= '--' . $argName . '=' . escapeshellarg($argValue) . ' ';
            }
        }

        $this->output->writeln('<info>Start installation process.</info>');
        $this->_runInstaller($installArgs);

        return true;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getInstallScriptPath()
    {
        $installerScript  = $this->config->getString('installationFolder') . DIRECTORY_SEPARATOR . 'setup/index.php';
        if (!file_exists($installerScript)) {
            throw new \Exception('Installation script was not found.');
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
        $defaultSessionFolder = $this->config->getString('installationFolder')
            . DIRECTORY_SEPARATOR
            . 'var/session';
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
     * @param array $installArgs
     *
     * @throws \Exception
     */
    protected function _runInstaller($installArgs)
    {
        $installationOutput = null;
        $returnStatus = null;

        if (OperatingSystem::isWindows()) {
            $installCommand = 'php ' . $this->getInstallScriptPath() . ' install ' . $installArgs;
        } else {
            $installCommand = '/usr/bin/env php ' . $this->getInstallScriptPath() . ' install ' . $installArgs;
        }

        $this->output->writeln('<comment>' . $installCommand . '</comment>');
        exec($installCommand, $installationOutput, $returnStatus);
        $installationOutput = implode(PHP_EOL, $installationOutput);
        if ($returnStatus !== self::EXEC_STATUS_OK) {
            throw new \Exception('Installation failed.' . $installationOutput);
        } else {
            $this->output->writeln('<info>Successfully installed Magento</info>');
            $encryptionKey = trim(substr($installationOutput, strpos($installationOutput, ':') + 1));
            $this->output->writeln('<comment>Encryption Key:</comment> <info>' . $encryptionKey . '</info>');
        }
    }
}