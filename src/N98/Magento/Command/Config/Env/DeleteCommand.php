<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Env;

use Dflydev\DotAccessData\Data;
use N98\Magento\Command\AbstractMagentoCommand;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteCommand
 * @package N98\Magento\Command\Config\Env
 */
class DeleteCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('config:env:delete')
            ->setDescription('Delete entry from env.php')
            ->addArgument('key', InputArgument::REQUIRED, 'config key')
            ->setHelp('Removes a specific config from the env.php file. Use config:env:show command to see the existing values.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $envFilePath = $this->getApplication()->getMagentoRootFolder() . '/app/etc/env.php';

        if (!file_exists($envFilePath)) {
            throw new RuntimeException('env.php file does not exist.');
        }

        $envConfig = include $envFilePath;
        $env = new Data($envConfig);

        $key = $input->getArgument('key');

        $checksumBefore = sha1(\json_encode($env->export(), JSON_THROW_ON_ERROR));
        $env->remove($key);
        $checksumAfter = sha1(\json_encode($env->export(), JSON_THROW_ON_ERROR));

        if ($checksumBefore !== $checksumAfter) {
            if (@file_put_contents(
                $envFilePath,
                "<?php\n\nreturn " . EnvHelper::exportVariable($env->export()) . ";\n"
            )
            ) {
                $output->writeln(sprintf('<info>Config <comment>%s</comment> successfully removed</info>', $key));
            } else {
                $output->writeln('<error>Config value could not be removed</error>');
            }
        } else {
            $output->writeln('<info>Config doesn\'t exists</info>');
        }

        return Command::SUCCESS;
    }
}
