<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database\Compressor;

/**
 * Class LZ4
 * @package N98\Magento\Command\Database\Compressor
 */
class LZ4 extends AbstractCompressor
{
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
            return $command . ' | lz4 -c ';
        } else {
            return sprintf(
                "tar -I 'lz4' -cf %s",
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
                return 'pv -cN lz4 ' . escapeshellarg($fileName) . ' | lz4 -d | pv -cN mysql | ' . $command;
            }

            return 'lz4 -dc < ' . escapeshellarg($fileName) . ' | ' . $command;
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
            if (substr($fileName, -4, 4) === '.lz4') {
                return $fileName;
            } elseif (substr($fileName, -4, 4) === '.sql') {
                $fileName .= '.lz4';
            } else {
                $fileName .= '.sql.lz4';
            }
        } elseif (substr($fileName, -8, 8) === '.tar.lz4') {
            return $fileName;
        } else {
            $fileName .= '.tar.lz4';
        }

        return $fileName;
    }
}
