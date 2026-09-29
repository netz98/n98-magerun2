<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility;

use PHPUnit\Framework\TestCase;

class MariaDbLifecycleClientTest extends TestCase
{
    private const HOST = '127.0.0.1';
    private const PORT = 18944;

    /** @var resource|null */
    private static $serverProcess;

    public static function setUpBeforeClass(): void
    {
        $docRoot = __DIR__ . '/_files';
        $command = sprintf(
            '%s -S %s:%d -t %s',
            escapeshellarg(PHP_BINARY),
            self::HOST,
            self::PORT,
            escapeshellarg($docRoot)
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

    private function apiUrl(): string
    {
        return sprintf('http://%s:%d/mariadb-releases.json', self::HOST, self::PORT);
    }

    public function testLookupReturnsMatchingRelease(): void
    {
        $client = new MariaDbLifecycleClient($this->apiUrl());

        $info = $client->lookup('10.11.6-MariaDB', 5);

        $this->assertNotNull($info);
        $array = $info->toArray();
        $this->assertSame('10.11', $array['releaseId']);
        $this->assertSame('Stable', $array['status']);
        $this->assertSame('Long Term Support', $array['supportType']);
        $this->assertSame('2028-02-16', $array['eolDate']);
    }

    public function testLookupReturnsNullForUnknownRelease(): void
    {
        $client = new MariaDbLifecycleClient($this->apiUrl());

        $this->assertNull($client->lookup('99.9.0-MariaDB', 5));
    }

    public function testLookupReturnsNullForNonVersionString(): void
    {
        $client = new MariaDbLifecycleClient($this->apiUrl());

        $this->assertNull($client->lookup('not-a-version', 5));
    }

    public function testLookupDegradesGracefullyWhenUnreachable(): void
    {
        $client = new MariaDbLifecycleClient('http://127.0.0.1:1/mariadb/');

        $this->assertNull($client->lookup('10.11.6-MariaDB', 1));
    }
}
