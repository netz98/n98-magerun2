<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility;

use Throwable;
use WpOrg\Requests\Requests;

/**
 * Best-effort lookup of MariaDB's own release lifecycle (status / support type / EOL date)
 * from the real MariaDB Foundation downloads REST API:
 * https://mariadb.org/downloads-rest-api/
 *
 * This is purely informational "database lifecycle" enrichment (issue #2141 asks for support
 * status to be reported separately from lifecycle information); it never affects the
 * supported/unsupported verdict, and any failure degrades to null ("unknown") rather than
 * throwing, bounded by a short timeout.
 */
class MariaDbLifecycleClient
{
    public const DEFAULT_API_URL = 'https://downloads.mariadb.org/rest-api/mariadb/';

    private string $apiUrl;

    public function __construct(string $apiUrl = self::DEFAULT_API_URL)
    {
        $this->apiUrl = $apiUrl;
    }

    public function lookup(string $dbVersion, int $timeoutSeconds = 5): ?LifecycleInfo
    {
        $releaseId = $this->majorMinor($dbVersion);
        if ($releaseId === null) {
            return null;
        }

        try {
            $response = Requests::get($this->apiUrl, [], [
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

        foreach ($payload['major_releases'] ?? [] as $release) {
            if (($release['release_id'] ?? null) === $releaseId) {
                return new LifecycleInfo(
                    $releaseId,
                    $release['release_status'] ?? null,
                    $release['release_support_type'] ?? null,
                    $release['release_eol_date'] ?? null,
                    'mariadb.org REST API'
                );
            }
        }

        return null;
    }

    private function majorMinor(string $dbVersion): ?string
    {
        if (preg_match('/^(\d+)\.(\d+)/', $dbVersion, $matches)) {
            return $matches[1] . '.' . $matches[2];
        }

        return null;
    }
}
