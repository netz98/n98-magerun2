<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\Console\Helper\MagentoHelper;

class ChooseInstallationFolder extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $input = $this->input;
        $validateInstallationFolder = function($folderName) use ($input) {
            $folderName = rtrim(trim($folderName, ' '), '/');
            if (substr($folderName, 0, 1) == '.') {
                $cwd = \getcwd() ;
                if (empty($cwd) && isset($_SERVER['PWD'])) {
                    $cwd = $_SERVER['PWD'];
                }
                $folderName = $cwd . substr($folderName, 1);
            }

            if (empty($folderName)) {
                throw new \InvalidArgumentException('Installation folder cannot be empty');
            }

            if (!is_dir($folderName)) {
                if (!@mkdir($folderName,0777, true)) {
                    throw new \InvalidArgumentException('Cannot create folder.');
                }

                return $folderName;
            }

            if ($input->hasOption('noDownload') && $input->getOption('noDownload')) {
                /** @var MagentoHelper $magentoHelper */
                $magentoHelper = new MagentoHelper();
                $magentoHelper->detect($folderName);
                if ($magentoHelper->getRootFolder() !== $folderName) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Folder %s is not a Magento working copy.',
                            $folderName
                        )
                    );
                }

                $localXml = $folderName . '/app/etc/local.xml';
                if (file_exists($localXml)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Magento working copy in %s seems already installed. Please remove %s and retry.',
                            $folderName,
                            $localXml
                        )
                    );
                }
            }

            return $folderName;
        };

        if (($installationFolder = $input->getOption('installationFolder')) == null) {
            $defaultFolder = './magento';
            $question[] = "<question>Enter installation folder:</question> [<comment>" . $defaultFolder . "</comment>]";

            $installationFolder = $this->getCommand()->getHelper('dialog')->askAndValidate($this->output, $question, $validateInstallationFolder, false, $defaultFolder);

        } else {
            // @Todo improve validation and bring it to 1 single function
            $installationFolder = $validateInstallationFolder($installationFolder);

        }

        $this->config->setString('installationFolder', realpath($installationFolder));
        \chdir($this->config->getString('installationFolder'));

        return true;
    }
}