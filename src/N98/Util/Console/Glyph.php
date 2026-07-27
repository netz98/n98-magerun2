<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console;

use N98\Util\OperatingSystem;

/**
 * Terminal glyphs with an ASCII fallback.
 *
 * Every symbol the UI draws goes through here so a terminal that cannot render the
 * box-drawing and symbol blocks (legacy Windows consoles, non-UTF-8 locales, TERM=dumb)
 * still produces readable output instead of mojibake.
 */
final class Glyph
{
    public const OK = 'ok';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';
    public const PENDING = 'pending';
    public const BULLET = 'bullet';
    public const ARROW = 'arrow';
    public const POINTER = 'pointer';
    public const ELLIPSIS = 'ellipsis';

    public const LINE = 'line';
    public const LINE_HEAVY = 'line-heavy';
    public const VERTICAL = 'vertical';
    public const TOP_LEFT = 'top-left';
    public const TOP_MID = 'top-mid';
    public const TOP_RIGHT = 'top-right';
    public const MID_LEFT = 'mid-left';
    public const CROSS = 'cross';
    public const MID_RIGHT = 'mid-right';
    public const BOTTOM_LEFT = 'bottom-left';
    public const BOTTOM_MID = 'bottom-mid';
    public const BOTTOM_RIGHT = 'bottom-right';

    /**
     * @var array<string, array{0: string, 1: string}> glyph name => [unicode, ascii]
     */
    private const GLYPHS = [
        self::OK => ["\u{2713}", '+'],
        self::ERROR => ["\u{2717}", 'x'],
        self::WARNING => ["\u{25B2}", '!'],
        self::INFO => ["\u{2022}", '-'],
        self::PENDING => ["\u{25CB}", 'o'],
        self::BULLET => ["\u{2022}", '*'],
        self::ARROW => ["\u{2192}", '->'],
        self::POINTER => ["\u{276F}", '>'],
        self::ELLIPSIS => ["\u{2026}", '...'],

        self::LINE => ["\u{2500}", '-'],
        self::LINE_HEAVY => ["\u{2501}", '='],
        self::VERTICAL => ["\u{2502}", '|'],
        self::TOP_LEFT => ["\u{250C}", '+'],
        self::TOP_MID => ["\u{252C}", '+'],
        self::TOP_RIGHT => ["\u{2510}", '+'],
        self::MID_LEFT => ["\u{251C}", '+'],
        self::CROSS => ["\u{253C}", '+'],
        self::MID_RIGHT => ["\u{2524}", '+'],
        self::BOTTOM_LEFT => ["\u{2514}", '+'],
        self::BOTTOM_MID => ["\u{2534}", '+'],
        self::BOTTOM_RIGHT => ["\u{2518}", '+'],
    ];

    /**
     * @var bool|null cached capability, null until first probe
     */
    private static $unicodeSupported = null;

    /**
     * Resolve a glyph for the current terminal.
     */
    public static function get(string $name): string
    {
        if (!isset(self::GLYPHS[$name])) {
            return '';
        }

        return self::GLYPHS[$name][self::supportsUnicode() ? 0 : 1];
    }

    /**
     * Repeat a glyph, e.g. to draw a horizontal rule.
     */
    public static function repeat(string $name, int $times): string
    {
        return $times > 0 ? str_repeat(self::get($name), $times) : '';
    }

    /**
     * True when the terminal can render the box-drawing and symbol blocks.
     *
     * Windows Terminal and ConEmu handle UTF-8 fine; the legacy conhost cannot, and there is no
     * reliable probe for which one is attached, so Windows only opts in when it advertises itself.
     */
    public static function supportsUnicode(): bool
    {
        if (self::$unicodeSupported !== null) {
            return self::$unicodeSupported;
        }

        return self::$unicodeSupported = self::probeUnicodeSupport();
    }

    /**
     * Force the capability, or pass null to re-probe. Intended for tests.
     */
    public static function setUnicodeSupported(?bool $supported): void
    {
        self::$unicodeSupported = $supported;
    }

    private static function probeUnicodeSupport(): bool
    {
        if (getenv('MAGERUN_ASCII') !== false) {
            return false;
        }

        if (getenv('TERM') === 'dumb') {
            return false;
        }

        if (OperatingSystem::isWindows()) {
            return getenv('WT_SESSION') !== false
                || getenv('ConEmuANSI') === 'ON'
                || getenv('TERM_PROGRAM') === 'vscode';
        }

        foreach (['LC_ALL', 'LC_CTYPE', 'LANG'] as $variable) {
            $value = getenv($variable);
            if ($value === false || $value === '') {
                continue;
            }

            return stripos($value, 'utf-8') !== false || stripos($value, 'utf8') !== false;
        }

        // No locale advertised at all. Modern terminals are UTF-8 by default and macOS ships
        // without LANG set in non-login shells, so assume support rather than degrading everyone.
        return true;
    }
}
