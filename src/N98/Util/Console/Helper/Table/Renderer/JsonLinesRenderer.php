<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * One JSON object per line renderer.
 */
class JsonLinesRenderer implements RendererInterface
{
    public function render(OutputInterface $output, array $rows)
    {
        foreach ($rows as $row) {
            $output->writeln(
                \json_encode($row, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
                OutputInterface::OUTPUT_RAW
            );
        }
    }
}
