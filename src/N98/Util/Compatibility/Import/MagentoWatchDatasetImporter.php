<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Import;

use DateTimeImmutable;
use N98\Util\Compatibility\Version;

/**
 * Transforms magento.watch's per-distribution version/system-requirements data (as fetched by
 * MagentoWatchClient) into db-compatibility.json rules[]/applications[]/sources[] entries, and
 * merges them into an existing dataset.
 *
 * Pure transformation, no I/O - callers fetch the source data and write the result.
 *
 * Every entry this importer writes gets a deterministic id prefixed with self::ID_PREFIX. On each
 * run it replaces only entries carrying that prefix and leaves everything else (hand-authored
 * rules, migrations[], freshnessPolicy, ...) untouched - so re-running it is always safe, and
 * removing a version from res/db-compatibility.json's "owned" set just means magento.watch no
 * longer reports it (it gets dropped on the next import rather than lingering as stale data).
 */
class MagentoWatchDatasetImporter
{
    public const ID_PREFIX = 'mw-';

    private const FAMILIES = ['mysql', 'mariadb'];

    /**
     * @param array $dataset the existing db-compatibility.json content (decoded)
     * @param array<string, array<string, array<string, mixed>>> $responsesByDistribution as
     *        returned by MagentoWatchClient::fetchAll()
     */
    public function import(array $dataset, array $responsesByDistribution, ?DateTimeImmutable $now = null): ImportResult
    {
        $now ??= new DateTimeImmutable();
        $today = $now->format('Y-m-d');

        $newRulesById = [];
        $newSourcesById = [];
        /** @var array<string, array{families: array<string, bool>, versions: string[]}> $scopes */
        $scopes = [];

        $byProduct = $this->groupResponsesByProduct($responsesByDistribution);

        foreach ($byProduct as $product => $versions) {
            foreach ($versions as $version => $perEdition) {
                foreach ($this->groupEditions($perEdition) as $group) {
                    $this->addRulesForVersion(
                        $product,
                        $version,
                        $group,
                        $today,
                        $newRulesById,
                        $newSourcesById,
                        $scopes
                    );
                }
            }
        }

        $newApplicationsById = $this->buildApplicationScopes($scopes);

        [$mergedRules, $added, $updated, $removed, $unchanged] = $this->mergeById(
            $dataset['rules'] ?? [],
            $newRulesById
        );
        [$mergedSources] = $this->mergeById($dataset['sources'] ?? [], $newSourcesById);
        [$mergedApplications] = $this->mergeById($dataset['applications'] ?? [], $newApplicationsById);

        $dataset['rules'] = $mergedRules;
        $dataset['sources'] = $mergedSources;
        $dataset['applications'] = $mergedApplications;

        if ($added !== [] || $updated !== [] || $removed !== []) {
            $dataset['datasetRevision'] = $this->nextRevision($dataset['datasetRevision'] ?? null, $now);
            $dataset['publishedAt'] = $today;
        }

        return new ImportResult(
            $dataset,
            $added,
            $updated,
            $removed,
            $unchanged,
            count($newSourcesById),
            count($newApplicationsById)
        );
    }

    /**
     * Reshapes {distribution => {version => entry}} into {product => {version => {edition => entry}}},
     * since magento-community/magento-commerce share a version axis (differentiated by edition) while
     * mage-os has its own, independent version numbering (differentiated by product).
     *
     * @return array<string, array<string, array<string, array<string, mixed>>>>
     */
    private function groupResponsesByProduct(array $responsesByDistribution): array
    {
        $map = [
            MagentoWatchClient::DISTRIBUTION_MAGENTO_COMMUNITY => ['magento', 'community'],
            MagentoWatchClient::DISTRIBUTION_MAGENTO_COMMERCE => ['magento', 'enterprise'],
            MagentoWatchClient::DISTRIBUTION_MAGE_OS => ['mage-os', 'community'],
        ];

        $byProduct = [];
        foreach ($map as $distribution => [$product, $edition]) {
            foreach ($responsesByDistribution[$distribution] ?? [] as $version => $entry) {
                $byProduct[$product][$version][$edition] = $entry + ['_distribution' => $distribution];
            }
        }

        return $byProduct;
    }

    /**
     * Decides, for one (product, version), whether community and enterprise editions can share a
     * single rule (identical mysql/mariadb requirements) or need to be split. mage-os only ever has
     * a "community" entry, so it always falls into the single-group case.
     *
     * @param array<string, array<string, mixed>> $perEdition editions present for this version
     * @return list<array{editions: string[], idSuffix: string, entries: array<string, array<string, mixed>>}>
     */
    private function groupEditions(array $perEdition): array
    {
        $community = $perEdition['community'] ?? null;
        $enterprise = $perEdition['enterprise'] ?? null;

        if ($community !== null && $enterprise !== null) {
            if ($this->requirementsEqual($community, $enterprise)) {
                return [[
                    'editions' => ['community', 'enterprise'],
                    'idSuffix' => '',
                    'entries' => ['community' => $community, 'enterprise' => $enterprise],
                ]];
            }

            return [
                ['editions' => ['community'], 'idSuffix' => '-ce', 'entries' => ['community' => $community]],
                ['editions' => ['enterprise'], 'idSuffix' => '-ee', 'entries' => ['enterprise' => $enterprise]],
            ];
        }

        if ($community !== null) {
            return [['editions' => ['community'], 'idSuffix' => '', 'entries' => ['community' => $community]]];
        }

        return [['editions' => ['enterprise'], 'idSuffix' => '', 'entries' => ['enterprise' => $enterprise]]];
    }

    private function requirementsEqual(array $a, array $b): bool
    {
        foreach (self::FAMILIES as $family) {
            $af = $a['systemRequirements'][$family] ?? [];
            $bf = $b['systemRequirements'][$family] ?? [];
            sort($af);
            sort($bf);
            if ($af !== $bf) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array{editions: string[], idSuffix: string, entries: array<string, array<string, mixed>>} $group
     */
    private function addRulesForVersion(
        string $product,
        string $version,
        array $group,
        string $today,
        array &$newRulesById,
        array &$newSourcesById,
        array &$scopes
    ): void {
        $editions = $group['editions'];
        $idSuffix = $group['idSuffix'];

        $sourceIds = [];
        foreach ($group['entries'] as $entry) {
            $distribution = $entry['_distribution'];
            $sourceId = self::ID_PREFIX . "$distribution-$version";
            $sourceIds[] = $sourceId;
            $newSourcesById[$sourceId] = [
                'id' => $sourceId,
                'title' => "magento.watch: $distribution $version",
                'url' => "https://magento.watch/api/v1/$distribution/versions/$version",
                'verifiedAt' => $today,
            ];
        }
        $sourceIds = array_values(array_unique($sourceIds));

        // Requirements are identical across the group by construction (single entry, or verified
        // equal in groupEditions()) - any entry gives the right systemRequirements.
        $entries = array_values($group['entries']);
        $entry = $entries[0];
        $editionsLabel = implode('/', $editions);

        foreach (self::FAMILIES as $family) {
            $majorMinors = $entry['systemRequirements'][$family] ?? [];
            if ($majorMinors === []) {
                continue;
            }

            foreach ($editions as $edition) {
                $scopeKey = "$product|$edition";
                $scopes[$scopeKey]['families'][$family] = true;
                $scopes[$scopeKey]['versions'][] = $version;
            }

            foreach ($majorMinors as $majorMinor) {
                $ruleId = self::ID_PREFIX . "$product-$version-$family-$majorMinor" . $idSuffix;
                $newRulesById[$ruleId] = [
                    'id' => $ruleId,
                    'product' => $product,
                    'editions' => $editions,
                    'contexts' => ['on-premise'],
                    'appVersionRange' => ['min' => $version, 'max' => $version],
                    'dbFamily' => $family,
                    'dbVersionRange' => ['min' => "$majorMinor.0", 'max' => "$majorMinor.99"],
                    'support' => 'supported',
                    'requirement' => sprintf(
                        '%s %s (%s) supports %s %s.x.',
                        $product,
                        $version,
                        $editionsLabel,
                        $family,
                        $majorMinor
                    ),
                    'sources' => $sourceIds,
                ];
            }
        }
    }

    /**
     * @param array<string, array{families: array<string, bool>, versions: string[]}> $scopes
     * @return array<string, array>
     */
    private function buildApplicationScopes(array $scopes): array
    {
        $applications = [];
        foreach ($scopes as $key => $info) {
            [$product, $edition] = explode('|', $key, 2);
            $id = self::ID_PREFIX . "$product-$edition";

            $versions = array_values(array_unique($info['versions']));
            usort($versions, static fn (string $a, string $b): int => version_compare(Version::normalize($a), Version::normalize($b)));

            $applications[$id] = [
                'id' => $id,
                'product' => $product,
                'edition' => $edition,
                'context' => 'on-premise',
                'versionRange' => ['min' => $versions[0], 'max' => $versions[count($versions) - 1]],
                'dbFamilyCoverage' => array_keys($info['families']),
                'exhaustive' => true,
                'vendor' => 'magento.watch',
                'sources' => [],
            ];
        }

        return $applications;
    }

    /**
     * Merges freshly generated entries into the existing collection: entries carrying
     * self::ID_PREFIX are fully owned by the importer (replaced, added, or dropped if no longer
     * produced); every other entry (hand-authored) is left exactly as-is.
     *
     * @return array{0: array, 1: string[], 2: string[], 3: string[], 4: string[]} merged collection,
     *         added ids, updated ids, removed ids, unchanged ids
     */
    private function mergeById(array $existing, array $newById): array
    {
        $merged = [];
        $seen = [];
        $added = [];
        $updated = [];
        $removed = [];
        $unchanged = [];

        foreach ($existing as $item) {
            $id = $item['id'];
            if (!str_starts_with($id, self::ID_PREFIX)) {
                $merged[] = $item;
                continue;
            }

            if (!isset($newById[$id])) {
                $removed[] = $id;
                continue;
            }

            $seen[$id] = true;
            if ($newById[$id] !== $item) {
                $updated[] = $id;
            } else {
                $unchanged[] = $id;
            }
            $merged[] = $newById[$id];
        }

        foreach ($newById as $id => $item) {
            if (!isset($seen[$id])) {
                $merged[] = $item;
                $added[] = $id;
            }
        }

        return [$merged, $added, $updated, $removed, $unchanged];
    }

    private function nextRevision(?string $current, DateTimeImmutable $now): string
    {
        $prefix = $now->format('Y.m');

        if ($current !== null && str_starts_with($current, "$prefix.")) {
            $n = (int) substr($current, strlen($prefix) + 1);

            return "$prefix." . ($n + 1);
        }

        return "$prefix.1";
    }
}
