<?php

namespace N98\Util\Console\Helper\Table\Renderer;

use const STDOUT;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class CsvRenderer
 * @package N98\Util\Console\Helper\Table\Renderer
 */
class CsvRenderer implements RendererInterface
{
    /**
     * @param OutputInterface $output
     * @param array           $rows
     */
    public function render(OutputInterface $output, array $rows)
    {
        if ($output instanceof StreamOutput) {
            $stream = $output->getStream();
        } else {
            $stream = STDOUT;
        }

        $i = 0;
        foreach ($rows as $row) {
            if ($i++ == 0) {
                fputcsv($stream, array_keys($row), escape: '"');
            }
            fputcsv($stream, $row, escape: '"');
        }
    }
}
