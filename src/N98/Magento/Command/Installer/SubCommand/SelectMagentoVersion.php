<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class SelectMagentoVersion extends AbstractSubCommand
{
    /**
     * Check PHP environment against minimal required settings modules
     *
     * @return void
     */
    public function execute()
    {
        if ($this->input->getOption('noDownload')) {
            return;
        }

        if (
            $this->input->getOption('magentoVersion') == null
            && $this->input->getOption('magentoVersionByName') == null
        ) {
            $question = array();
            foreach ($this->commandConfig['magento-packages'] as $key => $package) {
                $question[] = '<comment>' . str_pad('[' . ($key + 1) . ']', 4, ' ') . '</comment> ' .
                    $package['name'] . "\n";
            }
            $question[] = "<question>Choose a magento version:</question> ";

            $commandConfig = $this->commandConfig;


            $type = $this->getCommand()->getHelper('dialog')->askAndValidate(
                $this->output,
                $question,
                function ($typeInput) use ($commandConfig) {
                    if (!in_array($typeInput, range(1, count($this->commandConfig['magento-packages'])))) {
                        throw new \InvalidArgumentException('Invalid type');
                    }

                    return $typeInput;
                }
            );
        } else {
            $type = null;

            if ($this->input->getOption('magentoVersion')) {
                $type = $this->input->getOption('magentoVersion');
            } elseif ($this->input->getOption('magentoVersionByName')) {
                foreach ($this->commandConfig['magento-packages'] as $key => $package) {
                    if ($package['name'] == $this->input->getOption('magentoVersionByName')) {
                        $type = $key + 1;
                        break;
                    }
                }
            }

            if ($type == null) {
                throw new \InvalidArgumentException('Unable to locate Magento version');
            }
        }

        $this->config['magentoVersionData'] = $this->commandConfig['magento-packages'][$type - 1];
    }
}
