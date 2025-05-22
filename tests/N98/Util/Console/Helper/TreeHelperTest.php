<?php

namespace N98\Util\Console\Helper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class TreeHelperTest extends TestCase
{
    public function testTreePrinting()
    {
        $treeHelper = new TreeHelper();
        $treeHelper->setTitle("Test Tree");

        // Root 1
        $treeHelper->newNode("Root 1");
        $treeHelper->addValue("Leaf 1.1");
        // Node 1.2
        $treeHelper->newNode("Node 1.2");
        $treeHelper->addValue("Leaf 1.2.1");
        $treeHelper->end(); // End Node 1.2
        $treeHelper->addValue("Leaf 1.3");
        $treeHelper->end(); // End Root 1

        // Root 2
        $treeHelper->newNode("Root 2");
        $treeHelper->addValue("Leaf 2.1");
        $treeHelper->end(); // End Root 2

        $output = [];
        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock->method('writeln')
            ->willReturnCallback(function ($messages) use (&$output) {
                $output[] = $messages;
            });

        $treeHelper->printTree($outputMock);

        $expectedOutput = [
            "Test Tree",
            "├─ Root 1",
            "│  ├─ Leaf 1.1",
            "│  ├─ Node 1.2",
            "│  │  └─ Leaf 1.2.1",
            "│  └─ Leaf 1.3",
            "└─ Root 2",
            "   └─ Leaf 2.1",
        ];

        $this->assertEquals($expectedOutput, $output);
    }
}
