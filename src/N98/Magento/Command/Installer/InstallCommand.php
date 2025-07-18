<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Installer;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Installer\SubCommand\SubCommandFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallCommand
 *
 * @deprecated Deprecated since version 9.0.0, will be removed in v10.0.0. A new install command will be available in v10.0.0.
 * @codeCoverageIgnore  - Travis server uses installer to create a new shop. If it not works complete build fails.
 * @package N98\Magento\Command\Installer
 */
class InstallCommand extends AbstractMagentoCommand
{
    const EXEC_STATUS_OK = 0;

    /**
     * @var array
     */
    protected $commandConfig;

    /**
     * @var \Closure
     */
    protected $notEmptyCallback;

    /**
     * @var SubCommandFactory;
     */
    protected $subCommandFactory;

    protected function configure()
    {
        $this
            ->setName('install')
            ->addOption('magentoVersion', null, InputOption::VALUE_OPTIONAL, 'Magento version')
            ->addOption(
                'magentoVersionByName',
                null,
                InputOption::VALUE_OPTIONAL,
                'Magento version name instead of order number'
            )
            ->addOption('installationFolder', null, InputOption::VALUE_OPTIONAL, 'Installation folder')
            ->addOption('dbHost', null, InputOption::VALUE_OPTIONAL, 'Database host')
            ->addOption('dbUser', null, InputOption::VALUE_OPTIONAL, 'Database user')
            ->addOption('dbPass', null, InputOption::VALUE_OPTIONAL, 'Database password')
            ->addOption('dbName', null, InputOption::VALUE_OPTIONAL, 'Database name')
            ->addOption('dbPort', null, InputOption::VALUE_OPTIONAL, 'Database port', 3306)
            ->addOption('installSampleData', null, InputOption::VALUE_OPTIONAL, 'Install sample data')
            ->addOption(
                'useDefaultConfigParams',
                null,
                InputOption::VALUE_OPTIONAL,
                'Use default installation parameters defined in the yaml file'
            )
            ->addOption('baseUrl', null, InputOption::VALUE_OPTIONAL, 'Installation base url')
            ->addOption(
                'replaceHtaccessFile',
                null,
                InputOption::VALUE_OPTIONAL,
                'Generate htaccess file (for non vhost environment)'
            )
            ->addOption(
                'noDownload',
                null,
                InputOption::VALUE_NONE,
                'If set skips download step. Used when installationFolder is already a Magento installation that has ' .
                'to be installed on the given database.'
            )
            ->addOption(
                'only-download',
                null,
                InputOption::VALUE_NONE,
                'Downloads (and extracts) source code'
            )
            ->addOption(
                'forceUseDb',
                null,
                InputOption::VALUE_NONE,
                'If --forceUseDb passed, force to use given database if it already exists.'
            )
            ->addOption(
                'composer-use-same-php-binary',
                null,
                InputOption::VALUE_NONE,
                'If --composer-use-same-php-binary passed, will invoke composer with the same PHP binary'
            )
            ->setDescription('[DEPRECATED] This command will be removed in v10.0.0. Install magento');

        $help = <<<HELP
<warning>WARNING: This command is deprecated and will be removed in v10.0.0. A new install command will be available in that version.</warning>

* Download Magento by a list of git repos and zip files (mageplus,
  magelte, official community packages).
* Try to create database if it does not exist.
* Installs Magento sample data if available (since version 1.2.0).
* Starts Magento installer
* Sets rewrite base in .htaccess file

Example of an unattended Magento CE 2.0.0 installation:

   $ n98-magerun2.phar install --dbHost="localhost" --dbUser="mydbuser" \
     --dbPass="mysecret" --dbName="magentodb" --installSampleData=yes \
     --useDefaultConfigParams=yes \
     --magentoVersionByName="magento-ce-2.0.0" \
     --installationFolder="magento" --baseUrl="http://magento.localdomain/"

Additionally, with --noDownload option you can install Magento working 
copy already stored in --installationFolder on the given database.

See it in action: http://youtu.be/WU-CbJ86eQc

HELP;
        $this->setHelp($help);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<warning>WARNING: The \'install\' command is deprecated and will be removed in v10.0.0. A new install command will be available in that version.</warning>');
        $this->commandConfig = $this->getCommandConfig();
        $this->writeSection($output, 'Magento 2 Installation');

        $subCommandFactory = $this->createSubCommandFactory(
            $input,
            $output,
            'N98\Magento\Command\Installer\SubCommand' // sub-command namespace
        );

        // @todo load commands from config
        $subCommandFactory->create('PreCheckPhp')->execute();
        $subCommandFactory->create('SelectMagentoVersion')->execute();
        $subCommandFactory->create('ChooseInstallationFolder')->execute();
        $subCommandFactory->create('InstallComposer')->execute();

        $subCommandFactory->create('DownloadMagento')->execute();
        if ($input->getOption('only-download')) {
            return Command::SUCCESS;
        }

        //$subCommandFactory->create('InstallComposerPackages')->execute();
        $subCommandFactory->create('CreateDatabase')->execute();
        $subCommandFactory->create('RemoveEmptyFolders')->execute();
        $subCommandFactory->create('SetDirectoryPermissions')->execute();
        $subCommandFactory->create('InstallMagento')->execute();
        $subCommandFactory->create('RewriteHtaccessFile')->execute();
        $subCommandFactory->create('InstallSampleData')->execute();
        $subCommandFactory->create('PostInstallation')->execute();
        $output->writeln('<info>Successfully installed magento</info>');

        return Command::SUCCESS;
    }
}
