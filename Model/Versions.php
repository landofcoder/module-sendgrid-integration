<?php
namespace Lof\SendGrid\Model;
class Versions extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const TABLE = 'lof_sendgrid_template_version';

    protected function _construct()
    {
        $this->_init('Lof\SendGrid\Model\ResourceModel\Versions');
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
