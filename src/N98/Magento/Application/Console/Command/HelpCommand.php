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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand as BaseHelpCommand;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony's `help` with magerun's text descriptor.
 *
 * @see ListCommand for why the command rather than the helper is replaced
 */
class HelpCommand extends BaseHelpCommand
{
    /**
     * @var Command|null
     */
    private $describedCommand;

    /**
     * @return void
     */
    public function setCommand(Command $command)
    {
        $this->describedCommand = $command;

        parent::setCommand($command);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->describedCommand ??= $this->getApplication()->find($input->getArgument('command_name'));

        $helper = new DescriptorHelper();
        $helper->register('txt', new MagerunTextDescriptor());

        $helper->describe($output, $this->describedCommand, [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
        ]);

        $this->describedCommand = null;

        return 0;
    }
}
