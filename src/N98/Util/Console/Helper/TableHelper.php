<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper;

use N98\Magento\Command\CommandAware;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Util\Console\Helper\Table\Renderer\RendererInterface;
use N98\Util\Console\Helper\Table\TableStyleFactory;
use N98\Util\Console\Theme;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Text Table Helper
 * @author Timothy Anido <xanido@gmail.com>
 *
 * Based on draw_text_table by Paul Maunders
 * Available at http://www.pyrosoft.co.uk/blog/2007/07/01/php-array-to-text-table-function/
 */
class TableHelper extends AbstractHelper implements CommandAware
{
    use CommandTrait;

    /**
     * Narrowest a column may be squeezed to when fitting a wide table into the terminal.
     *
     * @var int
     */
    private const MIN_COLUMN_WIDTH = 8;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @var string|null heading drawn above the table on a terminal
     */
    protected $title;

    /**
     * Label the table, e.g. `STORES (2)`.
     *
     * Only drawn on a decorated terminal: the machine-readable `--format` renderers never see it,
     * and plain text output stays byte-identical to previous releases.
     *
     * @param string|null $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param OutputInterface $outputInterface
     * @param array $rows
     * @param string $format [optional]
     */
    public function renderByFormat(OutputInterface $outputInterface, array $rows, $format = null)
    {
        $rendererFactory = new RendererFactory();
        $renderer = $rendererFactory->create($format);

        if ($renderer && $renderer instanceof RendererInterface) {
            foreach ($rows as &$row) {
                if (!empty($this->headers)) {
                    $row = array_combine($this->headers, $row);
                }
            }

            $renderer->render($outputInterface, $rows);
        } else {
            $this->setRows($rows);
            $this->render($outputInterface);
        }
    }

    /**
     * Takes a two dimensional tabular array with headers as keys in the first row and outputs an ascii table
     *
     * @deprecated since 1.98.0 use original Symfony table instead.
     *
     * @param  OutputInterface $output
     * @param  array           $rows
     */
    public function write(OutputInterface $output, array $rows)
    {
        $this->setHeaders(array_keys($rows[0]));
        $this->setRows($rows);
        $this->render($output);
    }

    /**
     * @param OutputInterface $output
     * @param array $rows
     */
    public function render(OutputInterface $output, $rows = [])
    {
        if (empty($rows)) {
            $rows = $this->rows;
        }

        $table = new Table($output);
        $table->setHeaders($this->headers);
        $table->setRows($rows);

        if (!$output->isDecorated()) {
            // Piped, redirected and --no-ansi output keeps Symfony's stock rendering so scripts,
            // cron jobs and CI that parse magerun output are unaffected by the visual redesign.
            $table->setStyle(TableStyleFactory::plain());
            $table->render();

            return;
        }

        $this->renderTitle($output, count($rows));
        $table->setStyle(TableStyleFactory::dense());

        $widths = $this->naturalColumnWidths($rows, $output->getFormatter());
        $this->alignNumericColumns($table, $rows, array_keys($widths));
        $this->constrainToTerminalWidth($table, $widths);

        $table->render();
    }

    /**
     * Draw the table's heading and row count above it.
     */
    private function renderTitle(OutputInterface $output, int $rowCount): void
    {
        if ($this->title === null || $this->title === '' || Theme::colorDisabledByEnvironment()) {
            return;
        }

        // See MagerunStyle::__construct(): the heading's tags have to be registered on whatever
        // output we were handed, or the formatter would print them verbatim.
        Theme::apply($output);

        $output->writeln(Theme::headingLine($this->title, $rowCount));
    }

    /**
     * Right-align columns whose every populated cell is numeric, so digits line up by magnitude.
     *
     * @param array<int, int> $columns
     */
    private function alignNumericColumns(Table $table, array $rows, array $columns): void
    {
        foreach ($columns as $column) {
            if ($this->isNumericColumn($rows, $column)) {
                $table->setColumnStyle($column, TableStyleFactory::denseNumeric());
            }
        }
    }

    /**
     * Cap column widths so a wide table wraps inside the terminal instead of being chopped up by
     * the terminal's own line wrapping, which would destroy the column alignment entirely.
     *
     * Width is reclaimed from the widest column first and Symfony wraps rather than truncates,
     * so no data is lost.
     *
     * @param array<int, int> $widths natural width per column index
     */
    private function constrainToTerminalWidth(Table $table, array $widths): void
    {
        if ($widths === []) {
            return;
        }

        $natural = $widths;
        $budget = Theme::width() - (count($widths) * TableStyleFactory::SEPARATOR_WIDTH);

        if ($budget < count($widths) * self::MIN_COLUMN_WIDTH || array_sum($widths) <= $budget) {
            // Either the table already fits, or the terminal is too narrow for every column to
            // keep its floor - in which case shrinking would mangle the layout for no gain.
            return;
        }

        while (array_sum($widths) > $budget) {
            $widest = (int) array_search(max($widths), $widths, true);
            if ($widths[$widest] <= self::MIN_COLUMN_WIDTH) {
                break;
            }

            --$widths[$widest];
        }

        foreach ($widths as $column => $width) {
            if ($width < $natural[$column]) {
                $table->setColumnMaxWidth($column, $width);
            }
        }
    }

    /**
     * Visible width of the widest cell in each column, ignoring style tags and counting multibyte
     * characters as one.
     *
     * @return array<int, int> column index => width
     */
    private function naturalColumnWidths(array $rows, OutputFormatterInterface $formatter): array
    {
        $widths = [];

        foreach ($this->headers as $column => $header) {
            $widths[$column] = self::cellWidth($header, $formatter);
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $column => $cell) {
                if (!is_int($column)) {
                    continue;
                }

                $widths[$column] = max($widths[$column] ?? 0, self::cellWidth($cell, $formatter));
            }
        }

        ksort($widths);

        return $widths;
    }

    private function isNumericColumn(array $rows, int $column): bool
    {
        $seenValue = false;

        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row[$column])) {
                continue;
            }

            $value = trim((string) $row[$column]);
            if ($value === '') {
                continue;
            }

            if (!is_numeric($value)) {
                return false;
            }

            $seenValue = true;
        }

        return $seenValue;
    }

    /**
     * @param mixed $value
     */
    private static function cellWidth($value, OutputFormatterInterface $formatter): int
    {
        if ($value instanceof TableCell || $value instanceof TableSeparator) {
            // Spanning cells and separators do not map onto a single column width.
            return 0;
        }

        $width = 0;

        foreach (explode("\n", (string) $value) as $line) {
            $width = max($width, self::width(self::removeDecoration($formatter, $line)));
        }

        return $width;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'table';
    }

    /**
     * @param array $rows
     * @return $this
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @param array|string[] $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_values($headers);

        return $this;
    }
}
