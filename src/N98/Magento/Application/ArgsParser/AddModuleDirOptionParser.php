<?php

declare(strict_types=1);

namespace N98\Magento\Application\ArgsParser;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddModuleDirOptionParser
{
    /**
     * @var array
     */
    private array $args;

    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * Extracts and validates additional module directory paths from input or argv.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function parse(InputInterface $input, OutputInterface $output): array
    {
        $dirs = $input->getParameterOption('--add-module-dir', null, true);
        
        if ($dirs === null) {
            return [];
        }

        if (is_array($dirs)) {
            return array_values(array_filter($dirs, fn ($d) => is_string($d) && $d !== ''));
        }

        if (is_string($dirs) && $dirs !== '') {
            return [$dirs];
        }

        return [];
    }
}
