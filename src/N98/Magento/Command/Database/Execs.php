<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Command\Database;

use N98\Magento\Command\Database\Compressor\AbstractCompressor;

/**
 * One or multiple commands to execute, with support for Compressors
 *
 * @package N98\Magento\Command\Database
 */
class Execs
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $execs = [];

    /**
     * @var AbstractCompressor
     */
    private $compressor;

    /**
     * @var string|null
     */
    private $fileName;

    /**
     * Execs constructor.
     *
     * @param string|null $command [optional]
     */
    public function __construct($command = null)
    {
        $this->options = (array) $command;
    }

    /**
     * @param string $type of compression: "gz" | "gzip" | "none" | null
     */
    public function setCompression($type)
    {
        $this->compressor = AbstractCompressor::create($type);
    }

    /**
     * @return AbstractCompressor
     */
    public function getCompressor()
    {
        if (!$this->compressor) {
            $this->setCompression(null);
        }

        return $this->compressor;
    }

    /**
     * @param string $fileName output filename, will redirect mysqldump data into
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $options
     */
    public function addOptions($options)
    {
        $this->options[] = trim($options, ' ');
    }

    /**
     * @param string $options
     */
    public function add($options)
    {
        $this->execs[] = $options;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getBaseCommand($separator = '>')
    {
        $command = $this->getCompressor()->getCompressingCommand(
            implode(' ', $this->options)
        );

        if (strlen($this->fileName)) {
            $command .= ' ' . $separator . ' ' . escapeshellarg($this->fileName);
        }

        return $command;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        if (empty($this->execs)) {
            return [$this->getBaseCommand()];
        }

        $commands = [];
        foreach ($this->execs as $exec) {
            $next = clone $this;
            $next->options[] = trim($exec);
            $commands[] = $next->getBaseCommand($commands ? '>>' : '>');
        }

        return $commands;
    }
}
