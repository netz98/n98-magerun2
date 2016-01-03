<?php

namespace N98\Magento\Command\Developer\Console;

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
            $this->setCurrentModuleContext($output, $module);
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
     * @param $module
     */
    protected function setCurrentModuleContext(OutputInterface $output, $module)
    {
        $moduleList = $this->get(ModuleListInterface::class);
        /** @var $moduleList ModuleListInterface */

        $detectedModule = $moduleList->getOne($module);

        if ($detectedModule) {
            $this->setCurrentModuleName($module);

            $output->page($this->presenter->present($module, 1, 0));
            $output->writeln('<info>Use module </info><comment>' . $module . '</comment>');
            $this->getApplication()->setPrompt('Module: ' . $module . ' >>> ');
        } else {
            $output->writeln('<error>Invalid module</error>');
        }
    }
}