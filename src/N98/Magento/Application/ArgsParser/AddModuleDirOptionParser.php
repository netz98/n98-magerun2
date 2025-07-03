<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\ArgsParser;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddModuleDirOptionParser
{
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
