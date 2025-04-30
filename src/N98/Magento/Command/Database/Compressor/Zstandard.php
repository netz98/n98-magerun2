<?php

namespace N98\Magento\Command\Database\Compressor;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Class Zstandard
 * @package N98\Magento\Command\Database\Compressor
 */
class Zstandard extends AbstractCompressor
{
    protected int $compressionLevel;

    protected string $extraArgs;

    /**
     * @param InputInterface|null $input
     */
    public function __construct(?InputInterface $input = null)
    {
        $this->compressionLevel = $input ? (int)$input->getOption('zstd-level') : 10;
        $this->extraArgs = $input ? (string)$input->getOption('zstd-extra-args') : '';

        parent::__construct($input);
    }

    /**
     * Returns the command line for compressing the dump file.
     *
     * @param string $command
     * @param bool $pipe
     * @return string
     */
    public function getCompressingCommand($command, $pipe = true)
    {
        if ($pipe) {
            return sprintf(
                "%s | zstd -c -%s %s",
                $command,
                $this->compressionLevel,
                $this->extraArgs,
            );
        } else {
            return sprintf(
                "tar -I 'zstd %s -%s' -cf %s",
                $this->extraArgs,
                $this->compressionLevel,
                $command,
            );
        }
    }

    /**
     * Returns the command line for decompressing the dump file.
     *
     * @param string $command
     * @param string $fileName Filename (shell argument escaped)
     * @param bool $pipe
     * @return string
     */
    public function getDecompressingCommand($command, $fileName, $pipe = true)
    {
        if ($pipe) {
            if ($this->hasPipeViewer()) {
                return 'pv -cN zstd ' . escapeshellarg($fileName) . ' | zstd -d | pv -cN mysql | ' . $command;
            }

            return 'zstd -dc < ' . escapeshellarg($fileName) . ' | ' . $command;
        } else {
            if ($this->hasPipeViewer()) {
                return 'pv -cN tar -zxf ' . escapeshellarg($fileName) . ' && pv -cN mysql | ' . $command;
            }

            return 'tar -zxf ' . escapeshellarg($fileName) . ' -C ' . dirname($fileName) . ' && ' . $command . ' < '
                . escapeshellarg(substr($fileName, 0, -4));
        }
    }

    /**
     * Returns the file name for the compressed dump file.
     *
     * @param string $fileName
     * @param bool $pipe
     * @return string
     */
    public function getFileName($fileName, $pipe = true)
    {
        if ($fileName === null) {
            $fileName = '';
        }

        if (!strlen($fileName)) {
            return $fileName;
        }

        if ($pipe) {
            if (substr($fileName, -5, 5) === '.zstd') {
                return $fileName;
            } elseif (substr($fileName, -4, 4) === '.sql') {
                $fileName .= '.zstd';
            } else {
                $fileName .= '.sql.zstd';
            }
        } elseif (substr($fileName, -9, 9) === '.tar.zstd') {
            return $fileName;
        } else {
            $fileName .= '.tar.zstd';
        }

        return $fileName;
    }
}
