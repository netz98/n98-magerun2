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
 * Database release lifecycle information (status/support type/EOL date), kept strictly
 * separate from the compatibility support verdict - it never decides support/unsupported.
 */
class LifecycleInfo
{
    private string $releaseId;

    private ?string $status;

    private ?string $supportType;

    private ?string $eolDate;

    private string $source;

    public function __construct(string $releaseId, ?string $status, ?string $supportType, ?string $eolDate, string $source)
    {
        $this->releaseId = $releaseId;
        $this->status = $status;
        $this->supportType = $supportType;
        $this->eolDate = $eolDate;
        $this->source = $source;
    }

    public function toArray(): array
    {
        return [
            'releaseId' => $this->releaseId,
            'status' => $this->status,
            'supportType' => $this->supportType,
            'eolDate' => $this->eolDate,
            'source' => $this->source,
        ];
    }
}
