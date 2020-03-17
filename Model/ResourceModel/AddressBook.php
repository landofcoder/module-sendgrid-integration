<?php
namespace Lof\SendGrid\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

class AddressBook extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('lof_sendgrid_addressbook', 'id');
    }
}
