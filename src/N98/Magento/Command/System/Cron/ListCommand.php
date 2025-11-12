<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Cron;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\System\Cron
 */
class ListCommand extends AbstractCronCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:cron:list')
            ->setDescription('Lists all cronjobs')
            ->addArgument(
                'job_name',
                InputArgument::OPTIONAL,
                'Filter by job name. Wildcards with * are allowed.'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);

        $objectManager = $this->getObjectManager();

        /** @var AreaList $areaList */
        $areaList = $objectManager->get(AreaList::class);
        $areaList->getArea(Area::AREA_CRONTAB)
            ->load(Area::PART_CONFIG)
            ->load(Area::PART_TRANSLATE);

        if ($input->getOption('format') === null) {
            $this->writeSection($output, 'Cronjob List');
        }

        $table = $this->getJobs((string) $input->getArgument('job_name'));
        if (empty($table)) {
            $output->writeln('<info>No cron jobs found.</info>');

            return Command::SUCCESS;
        }
        $this->getHelper('table')
            ->setHeaders(array_keys(current($table)))
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
