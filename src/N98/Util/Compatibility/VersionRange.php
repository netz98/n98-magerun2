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
 * Inclusive [min, max] version boundary. Either side may be omitted (open-ended).
 */
class VersionRange
{
    private ?Version $min;

    private ?Version $max;

    public function __construct(?string $min, ?string $max)
    {
        $this->min = $min !== null ? new Version($min) : null;
        $this->max = $max !== null ? new Version($max) : null;
    }

    public static function fromArray(array $range): self
    {
        return new self($range['min'] ?? null, $range['max'] ?? null);
    }

    public function contains(string $version): bool
    {
        $candidate = new Version($version);

        if ($this->min !== null && !$candidate->isGreaterThanOrEqualTo($this->min)) {
            return false;
        }

        if ($this->max !== null && !$candidate->isLessThanOrEqualTo($this->max)) {
            return false;
        }

        return true;
    }
}
