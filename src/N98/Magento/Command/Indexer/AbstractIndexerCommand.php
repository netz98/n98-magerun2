<?php

namespace N98\Magento\Command\Indexer;

use N98\Magento\Command\AbstractMagentoCommand;

/**
 * Class AbstractIndexerCommand
 * @package N98\Magento\Command\Indexer
 */
class AbstractIndexerCommand extends AbstractMagentoCommand
{
    /**
     * @return array
     */
    protected function getIndexerList()
    {
        $list = [];
        $indexCollection = $this->getIndexerCollection();

        foreach ($indexCollection as $indexer) {
            /* @var $indexer \Magento\Indexer\Model\Indexer\DependencyDecorator */
            $list[] = [
                'view_id'         => $indexer->getViewId(),
                'code'            => $indexer->getId(),
                'title'           => $indexer->getTitle(),
                'status'          => $indexer->getStatus(),
                'last_updated'    => $indexer->getLatestUpdated(),
            ];
        }

        return $list;
    }

    /**
     * @return \Magento\Indexer\Model\Indexer\Collection
     */
    protected function getIndexerCollection()
    {
        return $this->getObjectManager()->get('Magento\Indexer\Model\Indexer\Collection');
    }
}
