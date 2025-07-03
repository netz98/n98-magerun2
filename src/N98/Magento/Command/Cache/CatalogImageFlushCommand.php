<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Cache;

use Magento\Catalog\Model\Product\Image;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CatalogImageFlushCommand extends AbstractMagentoCommand
{
    private Image $imageModel;

    protected function configure()
    {
        $this
            ->setName('cache:catalog:image:flush')
            ->addOption(
                'suppress-event',
                null,
                InputOption::VALUE_NONE,
                'Suppress clean_catalog_images_cache_after event dispatching'
            )
            ->setDescription('Flush catalog image cache')
        ;
    }

    public function inject(
        Image $imageModel
    ) {
        $this->imageModel = $imageModel;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initMagento();

        $this->imageModel->clearCache();
        $output->writeln('<info>Catalog image cache flushed</info>');

        if ($input->getOption('suppress-event')) {
            return self::SUCCESS;
        }

        $eventManager = $this->getObjectManager()->get(ManagerInterface::class);
        $eventManager->dispatch('clean_catalog_images_cache_after');

        return self::SUCCESS;
    }
}
