<?php

namespace N98\Util\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;

class TreeHelper
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $tree = [];

    /**
     * @var array
     */
    private $currentNode;

    /**
     * @var array
     */
    private $parents = [];

    public function __construct()
    {
        $this->currentNode = &$this->tree;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $value
     */
    public function newNode($value)
    {
        $node = ['value' => $value, 'children' => []];
        $this->currentNode[] = $node;
        $this->parents[] = &$this->currentNode; // Store reference to parent
        $this->currentNode = &$this->currentNode[count($this->currentNode) - 1]['children'];
    }

    /**
     * @param string $value
     */
    public function addValue($value)
    {
        $this->currentNode[] = ['value' => $value, 'children' => []];
    }

    public function end()
    {
        if (!empty($this->parents)) {
            $this->currentNode = &$this->parents[count($this->parents) - 1];
            array_pop($this->parents);
        } else {
            // Already at the root, or tree is empty
            $this->currentNode = &$this->tree;
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function printTree(OutputInterface $output)
    {
        if ($this->title) {
            $output->writeln($this->title);
        }
        $this->printNode($output, $this->tree);
    }

    /**
     * @param OutputInterface $output
     * @param array $nodes
     * @param string $prefix
     * @param bool $isLast
     */
    private function printNode(OutputInterface $output, array $nodes, $prefix = '', $isLast = false)
    {
        foreach ($nodes as $key => $node) {
            $isCurrentLast = ($key == count($nodes) - 1);
            $connector = $isCurrentLast ? '└─ ' : '├─ ';
            $output->writeln($prefix . $connector . $node['value']);

            $childPrefix = $prefix . ($isCurrentLast ? '   ' : '│  ');
            if (!empty($node['children'])) {
                $this->printNode($output, $node['children'], $childPrefix, $isCurrentLast);
            }
        }
    }
}
