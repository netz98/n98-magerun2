<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Translate;

use Magento\Framework\Translate\ResourceInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:translate:export')
            ->setDescription('Export inline translations')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Export filename')
            ->addOption(
                'store',
                null,
                InputOption::VALUE_OPTIONAL,
                'Limit to a special store'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $filename = $input->getArgument('filename');

        if (!$filename) {
            $filename = 'translate.csv';
        }

        $store = $this->getHelper('parameter')->askStore($input, $output);
        $locale = $input->getArgument('locale');
        $output->writeln('Exporting to <info>' . $filename . '</info>');

        $translate = $this->getObjectManager()->get(ResourceInterface::class);
        $result = $translate->getTranslationArray($store->getId(), $locale);

        $f = fopen($filename, 'w');

        foreach ($result as $key => $translation) {
            fputcsv($f, [$key, $translation]);
        }

        fclose($f);

        return Command::SUCCESS;
    }
}
