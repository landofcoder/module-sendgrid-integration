<?php
namespace Lof\SendGrid\Model\ResourceModel;


class Sender extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('lof_sendgrid_senders', 'id');
    }
}
