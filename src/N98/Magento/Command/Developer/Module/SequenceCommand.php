<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\ModuleList\Loader;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SequenceCommand extends AbstractMagentoCommand
{
    private Loader $moduleLoader;
    private Reader $configReader;
    private Writer $configWriter;

    public function inject(Loader $moduleLoader, Reader $configReader, Writer $configWriter): void
    {
        $this->moduleLoader = $moduleLoader;
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
    }

    protected function configure()
    {
        $this->setName('dev:module:sequence')
            ->setDescription('Regenerate the module sequence in app/etc/config.php')
            ->setHelp(
                'Order modules using the currently installed module.xml dependencies, without running '
                . 'setup:upgrade or database migrations. Preserve existing module states and unrelated config. '
                . 'Newly discovered modules are enabled; modules no longer present in the code are removed. '
                . 'This does not install or upgrade modules in the database.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->configReader->load(ConfigFilePool::APP_CONFIG);
        $currentModules = $config['modules'] ?? [];
        $modules = [];
        // Use the framework loader, not the setup Installer (which requires a separate setup DI context).
        foreach (array_keys($this->moduleLoader->load()) as $module) {
            $modules[$module] = $currentModules[$module] ?? 1;
        }

        if (isset($config['modules']) && $modules === $currentModules) {
            $output->writeln('<info>Module sequence is already up to date.</info>');

            return Command::SUCCESS;
        }

        // Replace the entire modules node, retaining all other app config sections.
        $this->configWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['modules' => $modules]], true);
        $output->writeln('<info>Module sequence regenerated in app/etc/config.php.</info>');

        return Command::SUCCESS;
    }
}
