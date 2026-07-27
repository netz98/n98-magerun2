<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\Console\Command;

use N98\Magento\Application\Console\Descriptor\MagerunTextDescriptor;
use Symfony\Component\Console\Command\ListCommand as BaseListCommand;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony's `list` with magerun's text descriptor.
 *
 * Symfony's ListCommand builds a DescriptorHelper inline rather than resolving one from the helper
 * set, so replacing the `txt` descriptor means replacing the command. The machine-readable formats
 * (xml, json, md) keep Symfony's own descriptors.
 */
class ListCommand extends BaseListCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = new DescriptorHelper();
        $helper->register('txt', new MagerunTextDescriptor());

        $helper->describe($output, $this->getApplication(), [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
            'namespace' => $input->getArgument('namespace'),
            'short' => $input->getOption('short'),
        ]);

        return 0;
    }
}
