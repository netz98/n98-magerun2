<?php

namespace N98\Magento\Command\Developer\Module;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\SubCommand\ConfigBag;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create a magento module skeleton
 */
class CreateCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:module:create')
            ->addArgument('vendorNamespace', InputArgument::REQUIRED, 'Namespace (your company prefix)')
            ->addArgument('moduleName', InputArgument::REQUIRED, 'Name of your module.')
            ->addOption('minimal', 'm', InputOption::VALUE_NONE, 'Create only module file')
            ->addOption('add-blocks', null, InputOption::VALUE_NONE, 'Adds blocks')
            ->addOption('add-helpers', null, InputOption::VALUE_NONE, 'Adds helpers')
            ->addOption('add-models', null, InputOption::VALUE_NONE, 'Adds models')
            ->addOption('add-setup', null, InputOption::VALUE_NONE, 'Adds SQL setup')
            ->addOption('add-all', null, InputOption::VALUE_NONE, 'Adds blocks, helpers and models')
            ->addOption('enable', 'e', InputOption::VALUE_NONE, 'Enable module after creation')
            ->addOption('modman', null, InputOption::VALUE_NONE, 'Create all files in folder with a modman file.')
            ->addOption('add-readme', null, InputOption::VALUE_NONE, 'Adds a readme.md file to generated module')
            ->addOption('add-composer', null, InputOption::VALUE_NONE, 'Adds a composer.json file to generated module')
            ->addOption('author-name', null, InputOption::VALUE_OPTIONAL, 'Author for readme.md or composer.json')
            ->addOption('author-email', null, InputOption::VALUE_OPTIONAL, 'Author for readme.md or composer.json')
            ->addOption('description', null, InputOption::VALUE_OPTIONAL, 'Description for readme.md or composer.json')
            ->setDescription('Create and register a new magento module.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $subCommandFactory = $this->createSubCommandFactory(
            $input,
            $output,
            'N98\Magento\Command\Developer\Module\Create\SubCommand' // sub-command namespace
        );

        $configBag = $subCommandFactory->getConfig();

        if (!$input->getOption('modman')) {
            $this->detectMagento($output);
        }

        $configBag->setBool('isModmanMode', $input->getOption('modman'));
        $configBag->setString('magentoRootFolder', $this->_magentoRootFolder);

        $this->initConfigBagDefaultValues($configBag);

        if ($input->getOption('add-all')) {
            $configBag->setBool('shouldAddBlocks', true);
            $configBag->setBool('shouldAddHelpers', true);
            $configBag->setBool('shouldAddModels', true);
            $configBag->setBool('shouldAddSetup', true);
        }

        if ($input->getOption('add-blocks')) {
            $configBag->setBool('shouldAddBlocks', true);
        }

        if ($input->getOption('add-helpers')) {
            $configBag->setBool('shouldAddHelpers', true);
        }

        if ($input->getOption('add-models')) {
            $configBag->setBool('shouldAddModels', true);
        }

        if ($input->getOption('add-setup')) {
            $configBag->setBool('shouldAddSetup', true);
        }

        if ($input->getOption('enable')) {
            $configBag->setBool('shouldEnableModule', true);
        }

        $configBag->setString('baseFolder', __DIR__ . '/../../../../../../res/module/create');
        $configBag->setString('vendorNamespace', ucfirst($input->getArgument('vendorNamespace')));
        $configBag->setString('moduleName', ucfirst($input->getArgument('moduleName')));

        $this->initView($input, $configBag);

        $subCommandFactory->create('CreateModuleFolders')->execute();
        $subCommandFactory->create('CreateModuleRegistrationFiles')->execute();

        if (!$input->getOption('minimal')) {
            $subCommandFactory->create('CreateModuleConfigFile')->execute();
            $subCommandFactory->create('CreateModuleDiFile')->execute();
            $subCommandFactory->create('CreateModuleEventsFile')->execute();
            $subCommandFactory->create('CreateModuleCrontabFile')->execute();
        }

        $subCommandFactory->create('EnableModule')->execute();

        if ($input->getOption('add-readme')) {
            $subCommandFactory->create('CreateReadmeFile')->execute();
        }

        if ($input->getOption('modman')) {
            $subCommandFactory->create('CreateModmanFile')->execute();
        }

        if ($input->getOption('add-composer')) {
            $subCommandFactory->create('CreateComposerFile')->execute();
        }

        if ($input->getOption('add-setup')) {
            $subCommandFactory->create('CreateSetupFiles')->execute();
        }

        if (!$input->getOption('minimal')) {
            $subCommandFactory->create('CreateAdditionalFiles')->execute();
        }
    }

    private function initView(InputInterface $input, ConfigBag $configBag)
    {
        $configBag->setArray('twigVars', array(
            'vendorNamespace' => $configBag->getString('vendorNamespace'),
            'moduleName'      => $configBag->getString('moduleName'),
            'createBlocks'    => $configBag->getBool('shouldAddBlocks'),
            'createModels'    => $configBag->getBool('shouldAddModels'),
            'createHelpers'   => $configBag->getBool('shouldAddHelpers'),
            'createSetup'     => $configBag->getBool('shouldAddSetup'),
            'authorName'      => $input->getOption('author-name'),
            'authorEmail'     => $input->getOption('author-email'),
            'description'     => $input->getOption('description'),
        ));
    }

    /**
     * @param ConfigBag $configBag
     */
    private function initConfigBagDefaultValues($configBag)
    {
        $configBag->setBool('shouldAddBlocks', false);
        $configBag->setBool('shouldAddHelpers', false);
        $configBag->setBool('shouldAddModels', false);
        $configBag->setBool('shouldAddSetup', false);
        $configBag->setBool('shouldEnableModule', false);
    }
}
