<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Indexer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Indexer
 */
class ListCommand extends AbstractIndexerCommand
{
    protected function configure()
    {
        $this
            ->setName('indexer:list')
            ->setDescription('Lists all magento indexes')
            ->addFormatOption();

        $help = <<<HELP
Lists all Magento indexers of current installation.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $table = [];
        foreach ($this->getIndexerList() as $index) {
            $table[] = [
                $index['code'],
                $index['title'],
                $index['status'],
                $index['last_updated'],
            ];
        }

        $this->getHelper('table')
            ->setTitle('Indexers')
            ->setHeaders(['code', 'title', 'status', 'last_updated'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
