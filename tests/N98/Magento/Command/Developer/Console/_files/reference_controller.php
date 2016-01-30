<?php

namespace N98\Dummy\Controller\Foo\Bar;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Baz extends Action
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }


}

