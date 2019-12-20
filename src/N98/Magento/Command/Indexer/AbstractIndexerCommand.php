<?php

namespace N98\Magento\Command\Indexer;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\DateTime as DateTimeUtils;

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
            /* @var $indexer \Magento\Indexer\Model\Indexer */
            $lastReadbleRuntime = $this->getRuntime($indexer);
            $runtimeInSeconds = $this->getRuntimeInSeconds($indexer);
            $list[] = [
                'code'            => $indexer->getId(),
                'title'           => $indexer->getTitle(),
                'status'          => $indexer->getStatus(),
                'last_runtime'    => $lastReadbleRuntime, // @TODO Check if this exists in Magento 2
                'runtime_seconds' => $runtimeInSeconds, // @TODO Check if this exists in Magento 2
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

    /**
     * Returns a readable runtime
     *
     * @param $indexer
     * @return mixed
     */
    protected function getRuntime($indexer)
    {
        $dateTimeUtils = new DateTimeUtils();
        $startTime = new \DateTime($indexer->getStartedAt());
        $endTime = new \DateTime($indexer->getEndedAt());
        if ($startTime > $endTime) {
            return 'index not finished';
        }
        $lastRuntime = $dateTimeUtils->getDifferenceAsString($startTime, $endTime);

        return $lastRuntime;
    }

    /**
     * Returns the runtime in total seconds
     *
     * @param $indexer
     * @return int
     */
    protected function getRuntimeInSeconds($indexer)
    {
        $startTimestamp = strtotime($indexer->getStartedAt());
        $endTimestamp = strtotime($indexer->getEndedAt());

        return $endTimestamp - $startTimestamp;
    }
}
