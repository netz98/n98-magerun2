<?php

namespace N98\Dummy\Console\Command\Foo\Bar;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BazCommand extends Command
{

    /**
     * Configures the current command.
     */
    public function configure()
    {
        $this->setName('n98:dummy:foo:bar:baz');
        $this->setDescription('n98:dummy:foo:bar:baz');
    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('BazCommand');
    }


}

