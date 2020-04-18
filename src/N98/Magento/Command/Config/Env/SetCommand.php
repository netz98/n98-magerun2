<?php

namespace N98\Magento\Command\Config\Env;

use Adbar\Dot;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetCommand
 * @package N98\Magento\Command\Config\Env
 */
class SetCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('config:env:set')
            ->setDescription('Set value in env.php')
            ->addArgument('key', InputArgument::REQUIRED, 'config key')
            ->addArgument('value', InputArgument::OPTIONAL, 'config value', '')
            ->setHelp('Modify config in env.php file. Use config:env:show command to see the existing values.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $envFilePath = $this->getApplication()->getMagentoRootFolder() . '/app/etc/env.php';

        if (!file_exists($envFilePath)) {
            throw new \RuntimeException('env.php file does not exist.');
        }

        $envConfig = include $envFilePath;
        $env = new Dot($envConfig);

        $key = $input->getArgument('key');
        $value = $input->getArgument('value');

        $env->set($key, $value);

        if (@file_put_contents(
            $envFilePath,
            "<?php\n\nreturn " . EnvHelper::exportVariable($env->all()) . ";\n"
        )
        ) {
            $output->writeln(sprintf('<info>Config <comment>%s</comment> successfully set to <comment>%s</comment></info>', $key, $value));
        } else {
            $output->writeln('<error>Config value could not be set</error>');
        }
    }
}
