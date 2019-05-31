<?php

namespace N98\Magento\Command\Config\Store;

use N98\Magento\Command\Config\AbstractConfigCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetCommand extends AbstractConfigCommand
{
    /**
     * @var array
     */
    protected $_scopes = array(
        'default',
        'websites',
        'stores',
    );

    protected function configure()
    {
        $this
            ->setName('config:store:set')
            ->setDescription('Set a store config item')
            ->addArgument('path', InputArgument::REQUIRED, 'The store config path like "general/local/code"')
            ->addArgument('value', InputArgument::REQUIRED, 'The config value')
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_OPTIONAL,
                'The config value\'s scope (default, websites, stores)',
                'default'
            )
            ->addOption('scope-id', null, InputOption::VALUE_OPTIONAL, 'The config value\'s scope ID', '0')
            ->addOption(
                'encrypt',
                null,
                InputOption::VALUE_NONE,
                'The config value should be encrypted using env.php\'s crypt key'
            )
            ->addOption(
                'no-null',
                null,
                InputOption::VALUE_NONE,
                'Do not treat value NULL as ' . self::DISPLAY_NULL_UNKNOWN_VALUE . ' value'
            )
        ;

        $help = <<<HELP
Set a store config value by path.
To set a value of a specify store view you must set the "scope" and "scope-id" option.

HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $scope = $input->getOption('scope');
        $this->_validateScopeParam($scope);
        $scopeId = $this->_convertScopeIdParam($scope, $input->getOption('scope-id'));

        $valueDisplay = $value = $input->getArgument('value');

        if ($value === 'NULL' && !$input->getOption('no-null')) {
            if ($input->getOption('encrypt')) {
                throw new \InvalidArgumentException('Encryption is not possbile for NULL values');
            }
            $value = null;
            $valueDisplay = self::DISPLAY_NULL_UNKNOWN_VALUE;
        } else {
            $value = str_replace(array('\n', '\r'), array("\n", "\r"), $value);
            $value = $this->_formatValue($value, ($input->getOption('encrypt') ? 'encrypt' : ''));
        }

        $this->getConfigWriter()->save(
            $input->getArgument('path'),
            $value,
            $scope,
            $scopeId
        );

        $output->writeln(
            '<comment>' . $input->getArgument('path') . '</comment> => <comment>' . $valueDisplay .
            '</comment>'
        );
    }
}
