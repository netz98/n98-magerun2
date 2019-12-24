<?php

namespace N98\Magento\Command\Script\Repository;

use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Script\Repository
 */
class ListCommand extends AbstractRepositoryCommand
{
    protected function configure()
    {
        $this
            ->setName('script:repo:list')
            ->setDescription('Lists all scripts in repository')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );

        $help = <<<HELP
You can organize your scripts in a repository.
Simply place a script in folder */usr/local/share/n98-magerun/scripts* or in your home dir
in folder *<HOME>/.n98-magerun/scripts*.

Scripts must have the file extension *.magerun*.

After that you can list all scripts with the *script:repo:list* command.
The first line of the script can contain a comment (line prefixed with #) which will be displayed as description.

   $ n98-magerun2.phar script:repo:list
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->getScripts();
        if (count($files) > 0) {
            $table = [];
            foreach ($files as $file) {
                $table[] = [
                    substr($file['fileinfo']->getFilename(), 0, -strlen(self::MAGERUN_EXTENSION)),
                    $file['location'],
                    $file['description'],
                ];
            }
        } else {
            $table = [];
        }

        if ($input->getOption('format') === null && count($table) === 0) {
            $output->writeln('<info>no script file found</info>');
        }

        $this->getHelper('table')
            ->setHeaders(['Script', 'Location', 'Description'])
            ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
