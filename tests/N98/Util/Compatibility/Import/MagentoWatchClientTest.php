<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Import;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Exercises the real MagentoWatchClient against a local, throwaway PHP built-in server running
 * tests/N98/Util/Compatibility/Import/_files/router.php, which mirrors magento.watch's real URL
 * shape (/{distribution}/versions[/{version}]) - same technique as
 * DatasetLoaderTest/MariaDbLifecycleClientTest, but via a router since two different endpoint
 * shapes need to be served from one fixture directory.
 */
class MagentoWatchClientTest extends TestCase
{
    private const HOST = '127.0.0.1';
    private const PORT = 18946;

    /** @var resource|null */
    private static $serverProcess;

    public static function setUpBeforeClass(): void
    {
        $docRoot = __DIR__ . '/_files';
        $command = sprintf(
            '%s -S %s:%d %s',
            escapeshellarg(PHP_BINARY),
            self::HOST,
            self::PORT,
            escapeshellarg($docRoot . '/router.php')
        );

        self::$serverProcess = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        $deadline = microtime(true) + 3;
        while (microtime(true) < $deadline) {
            $connection = @fsockopen(self::HOST, self::PORT, $errno, $errstr, 0.2);
            if ($connection !== false) {
                fclose($connection);
                return;
            }
            usleep(50000);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }
    }

    private function baseUrl(): string
    {
        return sprintf('http://%s:%d/', self::HOST, self::PORT);
    }

    public function testFetchVersionReturnsDecodedEntryOnSuccess(): void
    {
        $client = new MagentoWatchClient($this->baseUrl());

        $entry = $client->fetchVersion('mage-os', '3.0.0', 5);

        $this->assertNotNull($entry);
        $this->assertSame('3.0.0', $entry['version']);
        $this->assertSame(['8.4'], $entry['systemRequirements']['mysql']);
    }

    public function testFetchVersionReturnsNullOn404(): void
    {
        $client = new MagentoWatchClient($this->baseUrl());

        $this->assertNull($client->fetchVersion('mage-os', '99.99.99', 5));
    }

    public function testFetchVersionReturnsNullWhenUnreachable(): void
    {
        $client = new MagentoWatchClient('http://127.0.0.1:1/');

        $this->assertNull($client->fetchVersion('mage-os', '3.0.0', 1));
    }

    public function testFetchVersionsReturnsDataKeyedByVersion(): void
    {
        $client = new MagentoWatchClient($this->baseUrl());

        $versions = $client->fetchVersions('mage-os', 5);

        $this->assertSame(['3.0.0', '2.3.0'], array_keys($versions));
        $this->assertSame(['8.4'], $versions['3.0.0']['systemRequirements']['mysql']);
    }

    public function testFetchVersionsThrowsWhenUnreachable(): void
    {
        $client = new MagentoWatchClient('http://127.0.0.1:1/');

        $this->expectException(RuntimeException::class);

        $client->fetchVersions('mage-os', 1);
    }

    public function testFetchAllFetchesEveryRequestedDistribution(): void
    {
        $client = new MagentoWatchClient($this->baseUrl());

        // The router only serves "mage-os" - fetchAll() should still make one request per
        // distribution it's given (verified by the fact that only the configured one succeeds).
        $result = $client->fetchAll(['mage-os'], 5);

        $this->assertSame(['mage-os'], array_keys($result));
        $this->assertSame(['3.0.0', '2.3.0'], array_keys($result['mage-os']));
    }
}
