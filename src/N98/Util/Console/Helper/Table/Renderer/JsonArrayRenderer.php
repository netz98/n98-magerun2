<?php

namespace N98\Util\Console\Helper\Table\Renderer;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class JsonArrayRenderer
 * @package N98\Util\Console\Helper\Table\Renderer
 */
class JsonArrayRenderer implements RendererInterface
{
    /**
     * @param OutputInterface $output
     * @param array           $rows
     */
    public function render(OutputInterface $output, array $rows)
    {
        $rows = array_values($rows);
        $output->writeln(\json_encode($rows, JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT));
    }
}
