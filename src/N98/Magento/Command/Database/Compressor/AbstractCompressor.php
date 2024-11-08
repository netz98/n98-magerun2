<?php

namespace N98\Magento\Command\Database\Compressor;

use InvalidArgumentException;
use N98\Util\BinaryString;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class AbstractCompressor
 * @package N98\Magento\Command\Database\Compressor
 */
abstract class AbstractCompressor implements Compressor
{
    /**
     * @param InputInterface|null $input
     */
    public function __construct(InputInterface $input = null)
    {
    }

    /**
     * @param string $type
     * @param InputInterface $input
     * @return AbstractCompressor
     * @throws InvalidArgumentException
     */
    public static function create($type, InputInterface $input = null)
    {
        switch ($type) {
            case null:
            case 'none':
                return new Uncompressed($input);

            case 'gz':
            case 'gzip':
                return new Gzip($input);

            case 'zstd':
                return new Zstandard($input);

            case 'lz4':
                return new LZ4($input);

            default:
                throw new InvalidArgumentException("Compression type '{$type}' is not supported.");
        }
    }

    /**
     * @param string $filename
     * @return string|null
     */
    public static function tryGetCompressionType(string $filename)
    {
        switch (true) {
            case BinaryString::endsWith($filename, '.sql'):
                return 'none';
            case BinaryString::endsWith($filename, '.sql.zstd'):
            case BinaryString::endsWith($filename, '.tar.zstd'):
                return 'zstd';
            case BinaryString::endsWith($filename, '.sql.lz4'):
            case BinaryString::endsWith($filename, '.tar.lz4'):
                return 'lz4';
            case BinaryString::endsWith($filename, '.sql.gz'):
            case BinaryString::endsWith($filename, '.tgz'):
            case BinaryString::endsWith($filename, '.gz'):
                return 'gzip';
            default:
                return null;
        }
    }

    /**
     * Returns the command line for compressing the dump file.
     *
     * @param string $command
     * @param bool $pipe
     * @return string
     */
    abstract public function getCompressingCommand($command, $pipe = true);

    /**
     * Returns the command line for decompressing the dump file.
     *
     * @param string $command MySQL client tool connection string
     * @param string $fileName Filename (shell argument escaped)
     * @param bool $pipe
     * @return string
     */
    abstract public function getDecompressingCommand($command, $fileName, $pipe = true);

    /**
     * Returns the file name for the compressed dump file.
     *
     * @param string $fileName
     * @param bool $pipe
     * @return string
     */
    abstract public function getFileName($fileName, $pipe = true);

    /**
     * Check whether pv is installed
     *
     * @return bool
     */
    protected function hasPipeViewer()
    {
        return OperatingSystem::isProgramInstalled('pv');
    }
}
