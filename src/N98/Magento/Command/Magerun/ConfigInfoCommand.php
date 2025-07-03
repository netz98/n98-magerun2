<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Magerun;

use N98\Magento\Application\ConfigInfo;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigInfoCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('magerun:config:info')
            ->setDescription('Prints infos about the loaded config files')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = $this->getApplication()->getConfigurationLoader();

        $table = [];
        foreach ($loader->getLoadedConfigFiles() as $configInfo) {
            $table[] = [
                'type' => $configInfo->type,
                'path' => realpath($configInfo->path),
                'note' => $this->getNote($configInfo),
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['type', 'path', 'note'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }

    private function getNote(ConfigInfo $configInfo): string
    {
        switch ($configInfo->type) {
            case ConfigInfo::TYPE_DIST:
                return 'Shipped in phar file';

            case ConfigInfo::TYPE_SYSTEM:
                return 'Global configuration on system level';

            case ConfigInfo::TYPE_USER:
                return 'Configuration in home directory of current user';

            case ConfigInfo::TYPE_PLUGIN:
                return 'Configration is provided by a 3rd party extension';

            case ConfigInfo::TYPE_PROJECT:
                return 'The config is stored in the currently used project';

            default:
        }

        return '';
    }
}
