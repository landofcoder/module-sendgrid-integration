<?php
namespace Lof\SendGrid\Model;
class Sender extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const TABLE = 'lof_sendgrid_senders';

    protected function _construct()
    {
        $this->_init('Lof\SendGrid\Model\ResourceModel\Sender');
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
