<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Markdown;

/**
 * Class VersionFilePrinter
 * @package N98\Util\Markdown
 */
class VersionFilePrinter
{
    /**
     * @var string
     */
    private $content;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @param string $startVersion
     * @return string
     */
    public function printFromVersion($startVersion)
    {
        $contentToReturn = '';

        $lines = preg_split("/((\r?\n)|(\r\n?))/", $this->content);
        $versionPattern = '/^' . preg_quote($startVersion, '/') . '(\s+\(.*\))?$/';

        foreach ($lines as $line) {
            if (preg_match($versionPattern, $line)) {
                break;
            }

            $contentToReturn .= $line . "\n";
        }

        return trim($contentToReturn) . "\n";
    }
}
