<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

/**
 * Tab-separated table renderer.
 */
class TsvRenderer extends CsvRenderer
{
    protected $delimiter = "\t";
}
