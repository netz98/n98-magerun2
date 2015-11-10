<?php
namespace N98\Magento\Command\Cache;

use Magento\Framework\App\Cache\Type\ConfigSegment;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class EnableCommand extends AbstractModifierCommand
{
    const INVALID_TYPES_MESSAGE = '<error>The following cache types do not exist or are already enabled: %s</error>';

    const ABORT_MESSAGE = '<info>Nothing to do!</info>';

    const EXCEPTION_MESSAGE = '<error>Something went wrong: %s</error>';

    const SUCCESS_MESSAGE = '<info>The following cache types were enabled: <comment>%s</comment></info>';

    const TARGET_IS_ENABLED = 1;

    protected function configure()
    {
        $this
            ->setName('cache:enable')
            ->setDescription('Enables Magento caches')
            ->addArgument(
                'type',
                InputArgument::IS_ARRAY,
                'Type of cache to enable (separate multiple types with a space)'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }
}
