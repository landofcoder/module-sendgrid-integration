<?php
namespace Lof\SendGrid\Model\ResourceModel\Event;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Lof\SendGrid\Model\Event',
            'Lof\SendGrid\Model\ResourceModel\Event'
        );
    }
}
