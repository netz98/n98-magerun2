<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console\Helper\Table;

use N98\Util\Console\Glyph;
use Symfony\Component\Console\Helper\TableStyle;

/**
 * Builds the table styles used across magerun.
 *
 * The dense style drops the outer frame and keeps only the column separators plus three
 * horizontal rules, which reads better than a fully boxed table once a listing has more than a
 * handful of rows - the eye follows the columns instead of the frame.
 */
final class TableStyleFactory
{
    /**
     * Number of characters a column separator occupies including its surrounding padding.
     */
    public const SEPARATOR_WIDTH = 3;

    /**
     * The modern, frameless style used on interactive terminals.
     */
    public static function dense(): TableStyle
    {
        $horizontal = Glyph::get(Glyph::LINE);

        return (new TableStyle())
            ->setHorizontalBorderChars($horizontal)
            // A space, not an empty string, for the outer edge: Symfony budgets one character for
            // it when drawing the horizontal rules, so an empty one leaves every rule a character
            // wider than the rows it separates.
            ->setVerticalBorderChars(' ', Glyph::get(Glyph::VERTICAL))
            ->setCrossingChars(
                Glyph::get(Glyph::CROSS),
                $horizontal,
                Glyph::get(Glyph::TOP_MID),
                $horizontal,
                $horizontal,
                $horizontal,
                Glyph::get(Glyph::BOTTOM_MID),
                $horizontal,
                $horizontal
            )
            ->setBorderFormat('<border>%s</border>')
            ->setCellHeaderFormat('<subheading>%s</subheading>');
    }

    /**
     * A right-aligned variant of the dense style, applied to numeric columns.
     */
    public static function denseNumeric(): TableStyle
    {
        return self::dense()->setPadType(STR_PAD_LEFT);
    }

    /**
     * Symfony's stock style, used verbatim for non-decorated output.
     *
     * Piped, redirected and `--no-ansi` output must stay byte-identical to previous releases so
     * scripts, cron jobs and CI parsing magerun output keep working.
     */
    public static function plain(): TableStyle
    {
        return new TableStyle();
    }
}
