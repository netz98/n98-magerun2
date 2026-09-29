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
 * Result of evaluating one EnvironmentContext against the compatibility dataset.
 *
 * A "supported" result only confirms dataset-based version compatibility; it does not
 * certify schema, data, extension, or migration compatibility.
 */
class CompatibilityAssessment
{
    public const STATUS_SUPPORTED = 'supported';
    public const STATUS_UNSUPPORTED = 'unsupported';
    public const STATUS_UNKNOWN = 'unknown';

    private string $status;

    private string $findingId;

    private string $requirement;

    /** @var string[] */
    private array $reasons;

    private ?string $matchedRuleId;

    /** @var array<int, array{id: string, title: ?string, url: ?string, verifiedAt: ?string}> */
    private array $evidence;

    /** @var array<int, array{version: ?string, rationale: ?string, recommendedBy: ?string}> */
    private array $recommendations;

    public function __construct(
        string $status,
        string $findingId,
        string $requirement,
        array $reasons,
        ?string $matchedRuleId,
        array $evidence,
        array $recommendations
    ) {
        $this->status = $status;
        $this->findingId = $findingId;
        $this->requirement = $requirement;
        $this->reasons = $reasons;
        $this->matchedRuleId = $matchedRuleId;
        $this->evidence = $evidence;
        $this->recommendations = $recommendations;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isUnsupported(): bool
    {
        return $this->status === self::STATUS_UNSUPPORTED;
    }

    public function findingId(): string
    {
        return $this->findingId;
    }

    public function requirement(): string
    {
        return $this->requirement;
    }

    /** @return string[] */
    public function reasons(): array
    {
        return $this->reasons;
    }

    public function matchedRuleId(): ?string
    {
        return $this->matchedRuleId;
    }

    public function evidence(): array
    {
        return $this->evidence;
    }

    public function recommendations(): array
    {
        return $this->recommendations;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'findingId' => $this->findingId,
            'requirement' => $this->requirement,
            'reasons' => $this->reasons,
            'matchedRuleId' => $this->matchedRuleId,
            'evidence' => $this->evidence,
            'recommendations' => $this->recommendations,
        ];
    }
}
