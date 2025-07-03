<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface RendererInterface
 * @package N98\Util\Console\Helper\Table\Renderer
 */
interface RendererInterface
{
    /**
     * @param OutputInterface $output
     * @param array $rows
     * @return void
     */
    public function render(OutputInterface $output, array $rows);
}
