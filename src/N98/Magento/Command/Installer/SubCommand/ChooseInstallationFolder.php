<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use Symfony\Component\Console\Question\Question;

/**
 * Class ChooseInstallationFolder
 * @package N98\Magento\Command\Installer\SubCommand
 */
class ChooseInstallationFolder extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $input = $this->input;

        $installationFolder = $input->getOption('installationFolder');
        if ($installationFolder === null) {
            $defaultFolder = './magento';
            $question = new Question(
                sprintf(
                    '<question>Enter installation folder:</question> [<comment>%s</comment>]',
                    $defaultFolder
                ),
                $defaultFolder
            );
            $question->setValidator([$this, 'validateInstallationFolder']);

            $installationFolder = $this->getCommand()->getHelper('question')->ask(
                $this->input,
                $this->output,
                $question
            );
        } else {
            $installationFolder = $this->validateInstallationFolder($installationFolder);
        }

        $this->config->setString('initialFolder', getcwd());
        $this->config->setString('installationFolder', realpath($installationFolder));
        \chdir($this->config->getString('installationFolder'));

        return true;
    }

    /**
     * @param string $folderName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function validateInstallationFolder($folderName)
    {
        $folderName = rtrim(trim($folderName, ' '), '/');
        if (!empty($folderName) && $folderName[0] === '.') {
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
            if (!mkdir($folderName, 0777, true) && !is_dir($folderName)) {
                throw new \InvalidArgumentException('Cannot create folder.');
            }
        }

        return $folderName;
    }
}
