<?php

namespace N98\Magento\Application;

use RuntimeException;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\ConsoleOutput;

class Magento1Initializer
{
    /**
     * @var \Symfony\Component\Console\Helper\HelperSet
     */
    private $helperSet;

    /**
     * Magento1Initializer constructor.
     * @param \Symfony\Component\Console\Helper\HelperSet $helperSet
     */
    public function __construct(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * @return \N98\Magento\Framework\App\Magerun
     * @throws \Exception
     */
    public function init()
    {
        $magentoHint = <<<MAGENTOHINT
You are running a Magento 1.x instance. This version of n98-magerun is not compatible
with Magento 1.x. Please use n98-magerun (version 1) for this shop.

A current version of the software can be downloaded from the website:

<info>Download with curl
------------------</info>

    <comment>curl -O https://files.magerun.net/n98-magerun.phar</comment>

<info>Download with wget
------------------</info>

    <comment>wget https://files.magerun.net/n98-magerun.phar</comment>

MAGENTOHINT;

        $output = new ConsoleOutput();

        /** @var $formatter FormatterHelper */
        $formatter = $this->helperSet->get('formatter');

        $output->writeln([
            '',
            $formatter->formatBlock('Compatibility Notice', 'bg=blue;fg=white', true),
            '',
            $magentoHint,
        ]);

        throw new RuntimeException('This version of n98-magerun is not compatible with Magento 1');
    }
}
