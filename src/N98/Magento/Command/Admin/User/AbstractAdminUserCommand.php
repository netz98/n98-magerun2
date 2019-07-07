<?php

namespace N98\Magento\Command\Admin\User;

use N98\Magento\Command\AbstractMagentoCommand;

abstract class AbstractAdminUserCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\User\Model\User
     */
    protected $userModel;

    /**
     * @var \Magento\User\Model\ResourceModel\User
     */
    protected $userResource;

    /**
     * @param \Magento\User\Model\User               $userModel
     * @param \Magento\User\Model\ResourceModel\User $userResource
     */
    public function inject(
        \Magento\User\Model\User $userModel,
        \Magento\User\Model\ResourceModel\User $userResource
    ) {
        $this->userModel = $userModel;
        $this->userResource = $userResource;
    }
}
