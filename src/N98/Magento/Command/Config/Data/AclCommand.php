<?php

namespace N98\Magento\Command\Config\Data;

use Magento\Framework\Acl\AclResource\Config\Reader\Filesystem as AclConfigReader;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\TreeHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AclCommand extends AbstractMagentoCommand
{
    /**
     * @var AclConfigReader
     */
    private $configReader;

    protected function configure()
    {
        $this
            ->setName('config:data:acl')
            ->setDescription('Prints acl.xml data as table');
    }

    /**
     * @param AclConfigReader $configReader
     */
    public function inject(AclConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->configReader->read();

        $tree = new TreeHelper();
        $tree->setTitle('ACL Tree');

        $this->recursiveSort($data['config']['acl']['resources']);

        foreach ($data['config']['acl']['resources'] as $row) {
            $this->renderNode($tree, $row);
        }

        $tree->printTree($output);

        return Command::SUCCESS;
    }

    /**
     * @param TreeHelper $tree
     * @param array      $row
     */
    protected function renderNode($tree, $row)
    {
        $tree = $tree->newNode($this->formatNode($row));

        foreach ($row['children'] as $child) {
            if (count($child['children']) > 0) {
                $this->renderNode($tree, $child);
            } else {
                $tree->addValue($this->formatNode($child));
            }
        }

        $tree->end();
    }

    /**
     * @param $row
     *
     * @return string
     */
    private function formatNode($row)
    {
        return sprintf('%d: <info>%s</info> [<comment>%s</comment>]', $row['sortOrder'], $row['title'], $row['id']);
    }

    /**
     * @param $array
     */
    public function recursiveSort(array &$array)
    {
        uasort($array, [$this, 'compareNodes']);

        foreach ($array as &$row) {
            if (count($row['children']) > 0) {
                $this->recursiveSort($row['children']);
            }
        }
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function compareNodes(array $a, array $b)
    {
        if ($a['sortOrder'] > $b['sortOrder']) {
            return 1;
        }
        if ($a['sortOrder'] < $b['sortOrder']) {
            return -1;
        }

        return 0;
    }
}
