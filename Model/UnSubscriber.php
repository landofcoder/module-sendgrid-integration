<?php
namespace Lof\SendGrid\Model;
class UnSubscriber extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const TABLE = 'lof_sendgrid_unsubscribers_group';

    protected function _construct()
    {
        $this->_init('Lof\SendGrid\Model\ResourceModel\UnSubscriber');
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
