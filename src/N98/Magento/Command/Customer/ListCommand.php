<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer;

use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Customer
 */
class ListCommand extends AbstractCustomerCommand
{
    protected function configure()
    {
        $this
            ->setName('customer:list')
            ->setDescription('Lists all magento customers')
            ->addArgument('search', InputArgument::OPTIONAL, 'Search query')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );

        $help = <<<HELP
Lists all Magento Customers of current installation.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $search = null;
        if ($input->getArgument('search')) {
            $search = $input->getArgument('search');
        }

        $table = [];
        foreach ($this->getCustomerList($search) as $index) {
            $table[] = [
                $index['id'],
                $index['firstname'],
                $index['lastname'],
                $index['email'],
                $index['website'],
                $index['created_at'],
            ];
        }

        if (count($table) > 0) {
            $helper = $this->getHelper('table');
            $helper->setHeaders(['id', 'firstname', 'lastname', 'email', 'website', 'created_at']);
            $helper->renderByFormat($output, $table, $input->getOption('format'));
        } else {
            $output->writeln('<comment>No customers found</comment>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
