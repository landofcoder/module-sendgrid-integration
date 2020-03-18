<?php
namespace Lof\SendGrid\Model\ResourceModel\Subscriber;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Lof\SendGrid\Model\Subscriber',
            'Lof\SendGrid\Model\ResourceModel\Subscriber');
    }
}
