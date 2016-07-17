<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Magento\Command\Developer\Console\Structure\ModuleNameStructure;
use N98\Util\BinaryString;
use Psy\VarDumper\Presenter;
use Psy\VarDumper\PresenterAware;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Module\ModuleListInterface;

class ModuleCommand extends AbstractGeneratorCommand implements PresenterAware
{
    /**
     * @var Presenter
     */
    private $presenter;

    protected function configure()
    {
        $this
            ->setName('module')
            ->addArgument('module', InputArgument::OPTIONAL)
            ->setAliases(['mod'])
            ->setDescription('Set current module context')
        ;
    }

    /**
     * PresenterAware interface.
     *
     * @param Presenter $presenter
     */
    public function setPresenter(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');

        if (!empty($module)) {
            $moduleName = new ModuleNameStructure($module);
            $this->setCurrentModuleContext($output, $moduleName);
        } else {
            try {
                $module = $this->getCurrentModuleName();
                $output->writeln('<info>Current module </info><comment>' . $module . '</comment>');
            } catch (\InvalidArgumentException $e) {
                $output->writeln('<info>No module context defined</info>');
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param ModuleNameStructure $moduleName
     */
    protected function setCurrentModuleContext(OutputInterface $output, ModuleNameStructure $moduleName)
    {
        $moduleList = $this->create(ModuleListInterface::class);
        /** @var $moduleList ModuleListInterface */

        $detectedModule = $moduleList->getOne($moduleName->getFullModuleName());

        if (is_array($detectedModule)) {
            $detectedModule = $detectedModule['name'];
        }

        if (!$detectedModule) {
            // Try to load first matching module
            foreach ($moduleList->getAll() as $moduleListItem) {
                if (BinaryString::startsWith($moduleListItem['name'], $moduleName->getFullModuleName())) {
                    $detectedModule = $moduleListItem['name'];
                    break;
                }
            }
        }

        if ($detectedModule) {
            $this->setCurrentModuleName($detectedModule);
            $output->writeln('<info>Use module </info><comment>' . $detectedModule . '</comment>');
            $this->getApplication()->setPrompt('Module: ' . $detectedModule . ' >>> ');
        } else {
            $output->writeln('<error>Invalid module</error>');
        }
    }
}