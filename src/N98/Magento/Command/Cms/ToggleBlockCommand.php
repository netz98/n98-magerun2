<?php

declare(strict_types=1);

namespace N98\Magento\Command\Cms;

use function is_string;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use N98\Magento\Command\AbstractMagentoCommand;
use function sprintf;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ToggleBlockCommand
 * @package N98\Magento\Command\Cms
 */
class ToggleBlockCommand extends AbstractMagentoCommand
{
    protected const BLOCK_ID_ARGUMENT = 'blockId';

    protected $blockRepository;

    public function inject(BlockRepositoryInterface $blockRepository): void
    {
        $this->blockRepository = $blockRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('cms:block:toggle')
            ->addArgument(self::BLOCK_ID_ARGUMENT, InputArgument::REQUIRED, 'Block identifier')
            ->setDescription('Toggle Cms Block status');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $blockId = $input->getArgument(self::BLOCK_ID_ARGUMENT);
        if (!is_string($blockId)) {
            $output->writeln('Block Identifier is a required argument. Use --help for more information.');
            return;
        }

        try {
            $block = $this->blockRepository->getById($blockId);
            $newStatus = !$block->isActive();
            $block->setIsActive($newStatus);
            $this->blockRepository->save($block);
            $output->writeln(
                sprintf('Block status has been changed to <info>%s</info>.', $newStatus ? 'Enabled' : 'Disabled')
            );
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('Block with ID <info>%s</info> does not exist.', $blockId));
        } catch (LocalizedException $e) {
            $output->writeln('Something went wrong while editing the block status.');
        }
    }
}
