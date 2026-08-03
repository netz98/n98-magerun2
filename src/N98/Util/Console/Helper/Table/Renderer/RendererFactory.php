<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

/**
 * Class RendererFactory
 * @package N98\Util\Console\Helper\Table\Renderer
 */
class RendererFactory
{
    protected static $formats = [
        'csv'        => 'N98\Util\Console\Helper\Table\Renderer\CsvRenderer',
        'tsv'        => 'N98\Util\Console\Helper\Table\Renderer\TsvRenderer',
        'json'       => 'N98\Util\Console\Helper\Table\Renderer\JsonRenderer',
        'json_array' => 'N98\Util\Console\Helper\Table\Renderer\JsonArrayRenderer',
        'jsonl'      => 'N98\Util\Console\Helper\Table\Renderer\JsonLinesRenderer',
        'yaml'       => 'N98\Util\Console\Helper\Table\Renderer\YamlRenderer',
        'markdown'   => 'N98\Util\Console\Helper\Table\Renderer\MarkdownRenderer',
        'xml'        => 'N98\Util\Console\Helper\Table\Renderer\XmlRenderer',
    ];

    protected static $aliases = [
        'yml'    => 'yaml',
        'md'     => 'markdown',
        'ndjson' => 'jsonl',
    ];

    /**
     * @param string $format
     *
     * @return bool|RendererInterface
     */
    public function create($format)
    {
        if (empty($format)) {
            $format = '';
        }

        $format = strtolower($format);
        $format = self::$aliases[$format] ?? $format;
        if (isset(self::$formats[$format])) {
            $rendererClass = self::$formats[$format];
            return new $rendererClass();
        }

        return false;
    }

    /**
     * @return array
     */
    public static function getFormats()
    {
        return array_keys(self::$formats);
    }
}
