<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console\Util\Config;

/**
 * Class FileWriter
 *
 * @package N98\Magento\Command\Developer\Console\Util\Config
 */
class FileWriter extends \DOMDocument
{
    /**
     * @var string
     */
    protected static $defaultXml = '<config></config>';

    /**
     * @param string $filepath
     * @return FileWriter
     */
    public static function createByFilepath($filepath)
    {
        $dom = new static('1.0', 'UTF-8'); // @phpstan-ignore-line
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;

        if (file_exists($filepath)) {
            $dom->load($filepath);
        } else {
            $dom->loadXML(static::$defaultXml);
        }

        return $dom;
    }
}
