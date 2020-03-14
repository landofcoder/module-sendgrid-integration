<?php
namespace Lof\SendGrid\Model\ResourceModel\Versions;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Lof\SendGrid\Model\Versions',
            'Lof\SendGrid\Model\ResourceModel\Versions');
    }
}
