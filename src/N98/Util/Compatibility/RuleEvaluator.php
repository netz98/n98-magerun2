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
 * Evaluates an EnvironmentContext against a validated compatibility dataset array
 * (see res/db-compatibility.schema.json).
 *
 * A missing rule is deliberately not the same as "unsupported": a dataset "application"
 * scope must declare its database-family coverage as exhaustive before the absence of a
 * matching rule is allowed to produce an "unsupported" verdict. Otherwise the result is
 * "unknown".
 */
class RuleEvaluator
{
    public function evaluate(array $dataset, EnvironmentContext $context): CompatibilityAssessment
    {
        $application = $this->matchApplication($dataset['applications'] ?? [], $context);

        if ($application === null) {
            return new CompatibilityAssessment(
                CompatibilityAssessment::STATUS_UNKNOWN,
                sprintf('%s-%s-%s-no-scope', $context->product(), $context->edition(), $context->appVersion()),
                sprintf(
                    'No dataset entry describes %s %s %s in context "%s"; compatibility cannot be assessed.',
                    $context->product(),
                    $context->edition(),
                    $context->appVersion(),
                    $context->deploymentContext()
                ),
                ['The dataset does not cover this product/edition/version/context combination.'],
                null,
                [],
                []
            );
        }

        $rule = $this->matchRule($dataset['rules'] ?? [], $context);

        if ($rule !== null) {
            return new CompatibilityAssessment(
                $rule['support'],
                $rule['id'],
                $rule['requirement'],
                [$rule['requirement']],
                $rule['id'],
                $this->resolveSources($dataset, $rule['sources'] ?? []),
                $this->resolveRecommendations($rule['recommended'] ?? null)
            );
        }

        $familyCovered = in_array($context->dbFamily(), $application['dbFamilyCoverage'] ?? [], true);
        $exhaustive = (bool) ($application['exhaustive'] ?? false);
        $findingId = sprintf('%s:%s:%s', $application['id'], $context->dbFamily(), $context->dbVersion());

        if (!$familyCovered) {
            if ($exhaustive) {
                return new CompatibilityAssessment(
                    CompatibilityAssessment::STATUS_UNSUPPORTED,
                    $findingId,
                    sprintf(
                        '%s is not an officially supported database family for %s %s %s.',
                        ucfirst($context->dbFamily()),
                        $context->product(),
                        $context->edition(),
                        $context->appVersion()
                    ),
                    ['The application scope declares an exhaustive list of supported database families, and this family is not in it.'],
                    null,
                    $this->resolveSources($dataset, $application['sources'] ?? []),
                    []
                );
            }

            return new CompatibilityAssessment(
                CompatibilityAssessment::STATUS_UNKNOWN,
                $findingId,
                sprintf(
                    'The dataset does not describe database family "%s" for %s %s %s.',
                    $context->dbFamily(),
                    $context->product(),
                    $context->edition(),
                    $context->appVersion()
                ),
                ['Coverage for this database family is not declared exhaustive, so absence does not mean unsupported.'],
                null,
                $this->resolveSources($dataset, $application['sources'] ?? []),
                []
            );
        }

        // A family being covered at all does not mean every version within it has been
        // rule-authored yet (new DB point/minor releases ship continuously); only an explicit
        // rule may declare a specific version range unsupported. `exhaustive` therefore only
        // governs whether an entirely unlisted *family* (e.g. PostgreSQL) counts as unsupported
        // above - here it is always "unknown".
        return new CompatibilityAssessment(
            CompatibilityAssessment::STATUS_UNKNOWN,
            $findingId,
            sprintf(
                'No rule in the dataset covers %s %s for %s %s %s yet.',
                $context->dbFamily(),
                $context->dbVersion(),
                $context->product(),
                $context->edition(),
                $context->appVersion()
            ),
            ['No explicit rule addresses this specific version yet; it has not been confirmed supported or unsupported.'],
            null,
            $this->resolveSources($dataset, $application['sources'] ?? []),
            []
        );
    }

    /**
     * Looks up migration guidance for a database-family transition (e.g. mysql -> mariadb),
     * independent of the support verdict itself.
     */
    public function findMigrationGuidance(array $dataset, string $fromFamily, string $toFamily, string $appVersion): ?array
    {
        foreach ($dataset['migrations'] ?? [] as $migration) {
            if ($migration['from'] !== $fromFamily || $migration['to'] !== $toFamily) {
                continue;
            }

            $range = VersionRange::fromArray($migration['applicableVersions'] ?? []);
            if (!$range->contains($appVersion)) {
                continue;
            }

            return $migration;
        }

        return null;
    }

    private function matchApplication(array $applications, EnvironmentContext $context): ?array
    {
        $best = null;
        $bestScore = -1;

        foreach ($applications as $application) {
            if ($application['product'] !== $context->product()) {
                continue;
            }

            $editionScore = $this->matchScore($application['edition'], $context->edition());
            if ($editionScore === null) {
                continue;
            }

            $contextScore = $this->matchScore($application['context'], $context->deploymentContext());
            if ($contextScore === null) {
                continue;
            }

            $range = VersionRange::fromArray($application['versionRange'] ?? []);
            if (!$range->contains($context->appVersion())) {
                continue;
            }

            $score = $editionScore + $contextScore;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $application;
            }
        }

        return $best;
    }

    private function matchRule(array $rules, EnvironmentContext $context): ?array
    {
        $best = null;
        $bestScore = -1;

        foreach ($rules as $rule) {
            if ($rule['product'] !== $context->product()) {
                continue;
            }

            if ($rule['dbFamily'] !== $context->dbFamily()) {
                continue;
            }

            $editionScore = $this->listMatchScore($rule['editions'] ?? ['any'], $context->edition());
            if ($editionScore === null) {
                continue;
            }

            $contextScore = $this->listMatchScore($rule['contexts'] ?? ['any'], $context->deploymentContext());
            if ($contextScore === null) {
                continue;
            }

            $appRange = VersionRange::fromArray($rule['appVersionRange'] ?? []);
            if (!$appRange->contains($context->appVersion())) {
                continue;
            }

            $dbRange = VersionRange::fromArray($rule['dbVersionRange'] ?? []);
            if (!$dbRange->contains($context->dbVersion())) {
                continue;
            }

            $score = $editionScore + $contextScore;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $rule;
            }
        }

        return $best;
    }

    private function matchScore(string $candidateValue, string $contextValue): ?int
    {
        if ($candidateValue === $contextValue) {
            return 2;
        }

        if ($candidateValue === 'any') {
            return 1;
        }

        return null;
    }

    private function listMatchScore(array $candidateValues, string $contextValue): ?int
    {
        if (in_array($contextValue, $candidateValues, true)) {
            return 2;
        }

        if (in_array('any', $candidateValues, true)) {
            return 1;
        }

        return null;
    }

    private function resolveSources(array $dataset, array $sourceIds): array
    {
        $sourcesById = [];
        foreach ($dataset['sources'] ?? [] as $source) {
            $sourcesById[$source['id']] = $source;
        }

        $resolved = [];
        foreach ($sourceIds as $sourceId) {
            $source = $sourcesById[$sourceId] ?? null;
            $resolved[] = [
                'id' => $sourceId,
                'title' => $source['title'] ?? null,
                'url' => $source['url'] ?? null,
                'verifiedAt' => $source['verifiedAt'] ?? null,
            ];
        }

        return $resolved;
    }

    private function resolveRecommendations(?array $recommended): array
    {
        if ($recommended === null) {
            return [];
        }

        return [[
            'version' => $recommended['version'] ?? null,
            'rationale' => $recommended['rationale'] ?? null,
            'recommendedBy' => $recommended['recommendedBy'] ?? null,
        ]];
    }
}
