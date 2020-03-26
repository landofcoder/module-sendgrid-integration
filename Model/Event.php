<?php
namespace Lof\SendGrid\Model;
class Event extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const TABLE = 'lof_sendgrid_events';

    protected function _construct()
    {
        $this->_init('Lof\SendGrid\Model\ResourceModel\Event');
    }

    public function getIdentities()
    {
        return [self::TABLE . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
