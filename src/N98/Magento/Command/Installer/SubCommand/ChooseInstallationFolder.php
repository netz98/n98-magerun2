<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class ChooseInstallationFolder extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $input = $this->input;
        $validateInstallationFolder = function ($folderName) use ($input) {
            $folderName = rtrim(trim($folderName, ' '), '/');
            if (substr($folderName, 0, 1) == '.') {
                $cwd = \getcwd();
                if (empty($cwd) && isset($_SERVER['PWD'])) {
                    $cwd = $_SERVER['PWD'];
                }
                $folderName = $cwd . substr($folderName, 1);
            }

            if (empty($folderName)) {
                throw new \InvalidArgumentException('Installation folder cannot be empty');
            }

            if (!is_dir($folderName)) {
                if (!@mkdir($folderName, 0777, true)) {
                    throw new \InvalidArgumentException('Cannot create folder.');
                }

                return $folderName;
            }

            return $folderName;
        };

        if (($installationFolder = $input->getOption('installationFolder')) == null) {
            $defaultFolder = './magento';
            $question[] = "<question>Enter installation folder:</question> [<comment>" . $defaultFolder . "</comment>]";

            $installationFolder = $this->getCommand()->getHelper('dialog')->askAndValidate(
                $this->output,
                $question,
                $validateInstallationFolder,
                false,
                $defaultFolder
            );
        } else {
            // @Todo improve validation and bring it to 1 single function
            $installationFolder = $validateInstallationFolder($installationFolder);
        }

        $this->config->setString('installationFolder', realpath($installationFolder));
        \chdir($this->config->getString('installationFolder'));

        return true;
    }
}
