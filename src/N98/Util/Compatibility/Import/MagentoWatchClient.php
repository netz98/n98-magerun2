<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Import;

use RuntimeException;
use Throwable;
use WpOrg\Requests\Requests;

/**
 * Fetches per-distribution release/system-requirements data from magento.watch
 * (https://magento.watch/api), an independent, open, no-auth community API tracking exact
 * per-release MySQL/MariaDB (and other) requirements for Magento Open Source, Adobe Commerce,
 * and Mage-OS.
 *
 * fetchVersions()/fetchAll() back the dev-time import tool
 * (see ../../../../../scripts/import-db-compatibility-dataset.php) and throw on failure, since a
 * failed bulk refresh should be loud. fetchVersion() backs db:compatibility's optional --online
 * live check instead, so it degrades gracefully (returns null) rather than throwing - a single
 * unreachable/unknown version must never turn into an uncaught exception for a live command run.
 */
class MagentoWatchClient
{
    public const DISTRIBUTION_MAGENTO_COMMUNITY = 'magento-community';
    public const DISTRIBUTION_MAGENTO_COMMERCE = 'magento-commerce';
    public const DISTRIBUTION_MAGE_OS = 'mage-os';

    public const ALL_DISTRIBUTIONS = [
        self::DISTRIBUTION_MAGENTO_COMMUNITY,
        self::DISTRIBUTION_MAGENTO_COMMERCE,
        self::DISTRIBUTION_MAGE_OS,
    ];

    public const DEFAULT_BASE_URL = 'https://magento.watch/api/v1/';

    private string $baseUrl;

    public function __construct(string $baseUrl = self::DEFAULT_BASE_URL)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Fetches every version magento.watch knows about for one distribution.
     *
     * @return array<string, array<string, mixed>> keyed by version string, e.g. "2.4.8-p4"
     */
    public function fetchVersions(string $distribution, int $timeoutSeconds = 15): array
    {
        $url = $this->baseUrl . rawurlencode($distribution) . '/versions';

        try {
            $response = Requests::get($url, [], [
                'timeout' => $timeoutSeconds,
                'connect_timeout' => $timeoutSeconds,
                'verify' => true,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException("Could not reach $url: " . $e->getMessage(), 0, $e);
        }

        if (!$response->success) {
            throw new RuntimeException("$url returned HTTP {$response->status_code}");
        }

        try {
            $payload = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException("$url did not return valid JSON: " . $e->getMessage(), 0, $e);
        }

        if (!is_array($payload) || !is_array($payload['data'] ?? null)) {
            throw new RuntimeException("$url returned an unexpected shape (missing \"data\" object)");
        }

        return $payload['data'];
    }

    /**
     * @param string[] $distributions defaults to ALL_DISTRIBUTIONS
     * @return array<string, array<string, array<string, mixed>>> keyed by distribution, then version
     */
    public function fetchAll(array $distributions = self::ALL_DISTRIBUTIONS, int $timeoutSeconds = 15): array
    {
        $result = [];
        foreach ($distributions as $distribution) {
            $result[$distribution] = $this->fetchVersions($distribution, $timeoutSeconds);
        }

        return $result;
    }

    /**
     * Live lookup of a single exact version, for db:compatibility's optional --online check.
     * Returns null on any failure (unreachable, timeout, unknown version, bad response) instead
     * of throwing - callers are expected to fall back to the bundled dataset.
     *
     * @return array<string, mixed>|null
     */
    public function fetchVersion(string $distribution, string $version, int $timeoutSeconds = 10): ?array
    {
        $url = $this->baseUrl . rawurlencode($distribution) . '/versions/' . rawurlencode($version);

        try {
            $response = Requests::get($url, [], [
                'timeout' => $timeoutSeconds,
                'connect_timeout' => $timeoutSeconds,
                'verify' => true,
            ]);
        } catch (Throwable $e) {
            return null;
        }

        if (!$response->success) {
            return null;
        }

        try {
            $payload = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return null;
        }

        if (!is_array($payload) || !is_array($payload['data'] ?? null)) {
            return null;
        }

        return $payload['data'];
    }
}
