<?php

namespace N98\Magento\Command\Database\Compressor;

use PHPUnit\Framework\TestCase;

class CompressorTest extends TestCase
{
    public function testUncompressedDecompressingCommandEscaping()
    {
        $compressor = new Uncompressed();
        $fileName = 'file;inject.sql';
        $command = 'mysql';

        $result = $compressor->getDecompressingCommand($command, $fileName);
        $this->assertStringContainsString("'file;inject.sql'", $result);
    }

    public function testGzipDecompressingCommandEscaping()
    {
        $compressor = new Gzip();
        $fileName = 'dir;inject/file.sql.gz';
        $command = 'mysql';

        // Test pipe = false case where dirname is used
        $result = $compressor->getDecompressingCommand($command, $fileName, false);
        $this->assertStringContainsString("'dir;inject'", $result);
    }

    public function testLZ4DecompressingCommandEscaping()
    {
        $compressor = new LZ4();
        $fileName = 'dir;inject/file.sql.lz4';
        $command = 'mysql';

        // Test pipe = false case where dirname is used
        $result = $compressor->getDecompressingCommand($command, $fileName, false);
        $this->assertStringContainsString("'dir;inject'", $result);
    }

    public function testZstandardDecompressingCommandEscaping()
    {
        $compressor = new Zstandard();
        $fileName = 'dir;inject/file.sql.zstd';
        $command = 'mysql';

        // Test pipe = false case where dirname is used
        $result = $compressor->getDecompressingCommand($command, $fileName, false);
        $this->assertStringContainsString("'dir;inject'", $result);
    }
}
