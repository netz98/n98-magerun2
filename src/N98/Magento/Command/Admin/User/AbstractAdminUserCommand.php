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
     * @param \Magento\User\Model\User $userModel
     */
    public function inject(
        \Magento\User\Model\User $userModel
    ) {
        $this->userModel = $userModel;
    }
}
