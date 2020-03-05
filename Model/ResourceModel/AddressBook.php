<?php
namespace Lof\SendGrid\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

class AddressBook
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
