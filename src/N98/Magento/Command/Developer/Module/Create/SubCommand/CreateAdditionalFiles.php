<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\CommandConfigAware;
use N98\Magento\Command\SubCommand\AbstractSubCommand;

class CreateAdditionalFiles extends AbstractSubCommand implements CommandConfigAware
{
    /**
     * @var array
     */
    protected $commandConfig;

    /**
     * @return boolean|null
     */
    public function execute()
    {
        $config = $this->commandConfig;

        if (isset($config['additionalFiles']) && is_array($config['additionalFiles'])) {
            foreach ($config['additionalFiles'] as $template => $outFileRaw) {
                $outFile = $this->_getOutfile($outFileRaw);
                if (!is_dir(dirname($outFile))) {
                    mkdir(dirname($outFile), 0777, true);
                }

                \file_put_contents(
                    $outFile,
                    $this->getCommand()->getHelper('twig')->render($template, $this->config->getArray('twigVars'))
                );

                $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');
            }
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    private function _getOutfile($filename)
    {
        $pathes = array(
            'rootDir'   => $this->config->getString('magentoRootFolder'),
            'moduleDir' => $this->config->getString('moduleDirectory'),
        );

        return $this->getCommand()->getHelper('twig')->renderString(
            $filename,
            array_merge($this->config->getArray('twigVars'), $pathes)
        );
    }

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig)
    {
        $this->commandConfig = $commandConfig;
    }
}
