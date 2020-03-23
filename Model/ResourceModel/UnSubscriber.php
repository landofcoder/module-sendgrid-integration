<?php
namespace Lof\SendGrid\Model\ResourceModel;


class UnSubscriber extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('lof_sendgrid_unsubscribers_group', 'id');
    }
}
