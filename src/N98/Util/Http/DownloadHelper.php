<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Http;

use RuntimeException;
use WpOrg\Requests\Hooks;
use WpOrg\Requests\Requests;

class DownloadHelper
{
    /**
     * @param list<string> $allowedHosts
     * @param list<string> $headers
     */
    public static function download(
        string $url,
        string $target,
        array $allowedHosts,
        int $maxBytes,
        array $headers = []
    ): void {
        self::assertAllowedUrl($url, $allowedHosts);
        $hooks = new Hooks();
        $hooks->register('requests.before_redirect', static function (string $location) use ($allowedHosts): void {
            self::assertAllowedUrl($location, $allowedHosts);
        });
        $hooks->register('request.progress', static function (string $chunk, int $bytesSoFar) use ($maxBytes): void {
            if ($bytesSoFar + strlen($chunk) > $maxBytes) {
                throw new RuntimeException(sprintf('Download exceeds the maximum allowed size of %d bytes.', $maxBytes));
            }
        });

        try {
            $response = Requests::get($url, $headers, [
                'connect_timeout' => 10,
                'filename' => $target,
                'follow_redirects' => true,
                'hooks' => $hooks,
                'max_bytes' => $maxBytes,
                'redirects' => 5,
                'timeout' => 30,
                'useragent' => 'n98-magerun2',
                'verify' => true,
                'verifyname' => true,
            ]);
        } catch (\WpOrg\Requests\Exception $exception) {
            @unlink($target);
            throw new RuntimeException('Could not download ' . $url . ': ' . $exception->getMessage(), 0, $exception);
        } catch (RuntimeException $exception) {
            @unlink($target);
            throw $exception;
        }

        if (!$response->success) {
            @unlink($target);
            throw new RuntimeException(sprintf('Could not download %s (HTTP %d).', $url, $response->status_code));
        }
    }

    /** @param list<string> $allowedHosts */
    private static function assertAllowedUrl(string $url, array $allowedHosts): void
    {
        $parts = parse_url($url);
        $scheme = is_array($parts) ? strtolower((string) ($parts['scheme'] ?? '')) : '';
        $host = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';
        if ($scheme !== 'https'
            || $host === ''
            || isset($parts['user'], $parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)
            || !in_array($host, $allowedHosts, true)
        ) {
            throw new RuntimeException('Refusing download URL: ' . $url);
        }
    }
}
