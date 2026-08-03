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
 * GitHub-flavoured Markdown table renderer.
 */
class MarkdownRenderer implements RendererInterface
{
    public function render(OutputInterface $output, array $rows)
    {
        if ($rows === []) {
            return;
        }

        $headers = array_keys($rows[0]);
        $output->writeln($this->row($headers), OutputInterface::OUTPUT_RAW);
        $output->writeln($this->row(array_fill(0, count($headers), '---')), OutputInterface::OUTPUT_RAW);

        foreach ($rows as $row) {
            $output->writeln($this->row(array_values($row)), OutputInterface::OUTPUT_RAW);
        }
    }

    private function row(array $cells): string
    {
        $cells = array_map(fn ($cell) => $this->escape($cell), $cells);

        return '| ' . implode(' | ', $cells) . ' |';
    }

    private function escape($value): string
    {
        return str_replace(
            ["\\", "|", "\r\n", "\r", "\n"],
            ["\\\\", "\\|", '<br>', '<br>', '<br>'],
            (string) $value
        );
    }
}
