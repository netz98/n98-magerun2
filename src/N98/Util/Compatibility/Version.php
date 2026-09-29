<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility;

/**
 * Comparable representation of a Magento/database version string, including
 * Magento-style patch releases such as "2.4.8-p1".
 */
class Version
{
    private string $raw;

    private string $normalized;

    public function __construct(string $raw)
    {
        $this->raw = trim($raw);
        $this->normalized = self::normalize($this->raw);
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function normalized(): string
    {
        return $this->normalized;
    }

    public function compareTo(Version $other): int
    {
        return version_compare($this->normalized, $other->normalized);
    }

    public function isGreaterThanOrEqualTo(Version $other): bool
    {
        return $this->compareTo($other) >= 0;
    }

    public function isLessThanOrEqualTo(Version $other): bool
    {
        return $this->compareTo($other) <= 0;
    }

    /**
     * Normalizes a raw version string into a dotted, version_compare()-friendly form.
     *
     * "2.4.8"    -> "2.4.8.0"
     * "2.4.8-p1" -> "2.4.8.1"
     * "2.4.8-p12"-> "2.4.8.12"
     * "10.11.6-MariaDB" -> "10.11.6.0" (vendor suffixes are stripped, not treated as patch level)
     */
    public static function normalize(string $raw): string
    {
        $value = trim($raw);

        if (preg_match('/^(?<base>\d+(?:\.\d+)*)-p(?<patch>\d+)$/i', $value, $matches)) {
            return $matches['base'] . '.' . $matches['patch'];
        }

        if (preg_match('/^(?<base>\d+(?:\.\d+)*)/', $value, $matches)) {
            return $matches['base'] . '.0';
        }

        return $value;
    }
}
