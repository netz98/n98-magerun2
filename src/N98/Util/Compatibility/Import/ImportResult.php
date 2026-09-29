<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Import;

class ImportResult
{
    private array $dataset;

    /** @var string[] */
    private array $addedRuleIds;

    /** @var string[] */
    private array $updatedRuleIds;

    /** @var string[] */
    private array $removedRuleIds;

    /** @var string[] */
    private array $unchangedRuleIds;

    private int $sourceCount;

    private int $applicationCount;

    public function __construct(
        array $dataset,
        array $addedRuleIds,
        array $updatedRuleIds,
        array $removedRuleIds,
        array $unchangedRuleIds,
        int $sourceCount,
        int $applicationCount
    ) {
        $this->dataset = $dataset;
        $this->addedRuleIds = $addedRuleIds;
        $this->updatedRuleIds = $updatedRuleIds;
        $this->removedRuleIds = $removedRuleIds;
        $this->unchangedRuleIds = $unchangedRuleIds;
        $this->sourceCount = $sourceCount;
        $this->applicationCount = $applicationCount;
    }

    public function dataset(): array
    {
        return $this->dataset;
    }

    /** @return string[] */
    public function addedRuleIds(): array
    {
        return $this->addedRuleIds;
    }

    /** @return string[] */
    public function updatedRuleIds(): array
    {
        return $this->updatedRuleIds;
    }

    /** @return string[] */
    public function removedRuleIds(): array
    {
        return $this->removedRuleIds;
    }

    /** @return string[] */
    public function unchangedRuleIds(): array
    {
        return $this->unchangedRuleIds;
    }

    public function hasChanges(): bool
    {
        return $this->addedRuleIds !== [] || $this->updatedRuleIds !== [] || $this->removedRuleIds !== [];
    }

    public function summary(): string
    {
        return sprintf(
            '%d rule(s) added, %d updated, %d removed, %d unchanged (magento.watch-sourced: %d source citations, %d application scopes)',
            count($this->addedRuleIds),
            count($this->updatedRuleIds),
            count($this->removedRuleIds),
            count($this->unchangedRuleIds),
            $this->sourceCount,
            $this->applicationCount
        );
    }
}
