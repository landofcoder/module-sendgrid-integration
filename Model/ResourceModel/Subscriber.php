<?php
namespace Lof\SendGrid\Model\ResourceModel;


class Subscriber extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('lof_sendgrid_subscribers_group', 'id');
    }
}
