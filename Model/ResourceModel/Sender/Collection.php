<?php
namespace Lof\SendGrid\Model\ResourceModel\Sender;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Lof\SendGrid\Model\Sender',
            'Lof\SendGrid\Model\ResourceModel\Sender');
    }
}
