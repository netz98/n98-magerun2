<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application;

use InvalidArgumentException;
use N98\Util\ArrayFunctions;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigFileParser
 *
 * @package N98\Magento\Application
 */
final class ConfigFile
{
    /**
     * @var string
     */
    private $buffer;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     * @return ConfigFile
     * @throws InvalidArgumentException if $path is invalid (can't be read for whatever reason)
     */
    public static function createFromFile($path)
    {
        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf("Config-file is not readable: '%s'", $path));
        }

        $configFile = new static();
        $configFile->loadFile($path);

        return $configFile;
    }

    /**
     * @param string $path
     */
    public function loadFile($path)
    {
        $this->path = $path;
        $buffer = file_get_contents($path);
        if (!is_string($buffer)) {
            throw new InvalidArgumentException(sprintf("Invalid path for config file: '%s'", $path));
        }

        $this->setBuffer($buffer);
    }

    /**
     * @param string $buffer
     */
    public function setBuffer($buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     * @param string $magentoRootFolder
     * @param SplFileInfo|null $file [optional]
     *
     * @return void
     */
    public function applyVariables($magentoRootFolder, ?SplFileInfo $file = null)
    {
        $replace = [
            '%module%' => $file ? $file->getPath() : '',
            '%root%'   => $magentoRootFolder,
        ];

        $this->buffer = strtr($this->buffer, $replace);
    }

    /**
     * @throws RuntimeException
     */
    public function toArray()
    {
        $result = Yaml::parse($this->buffer);

        if (!is_array($result)) {
            throw new RuntimeException(sprintf("Failed to parse config-file '%s'", $this->path));
        }

        return $result;
    }

    public function mergeArray(array $array)
    {
        $result = $this->toArray();

        return ArrayFunctions::mergeArrays($array, $result);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
