<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Cache;

use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DisableCommand extends AbstractModifierCommand
{
    const INVALID_TYPES_MESSAGE = '<error>The following cache types do not exist or are already disabled: %s</error>';

    const ABORT_MESSAGE = '<info>Nothing to do!</info>';

    const EXCEPTION_MESSAGE = '<error>Something went wrong: %s</error>';

    const SUCCESS_MESSAGE = '<info>The following cache types were disabled: <comment>%s</comment></info>';

    const TARGET_IS_ENABLED = 0;

    protected function configure()
    {
        $this
            ->setName('cache:disable')
            ->setDescription('Disables Magento caches')
            ->addArgument(
                'type',
                InputArgument::IS_ARRAY,
                'Type of cache to disable (separate multiple types with a space)'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }
}
