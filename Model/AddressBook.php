<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://www.landofcoder.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SengGrid
 * @copyright  Copyright (c) 2020 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\SendGrid\Model;

class AddressBook extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    protected function _construct()
    {
        $this->_init('Lof\SendGrid\Model\ResourceModel\AddressBook');
    }
    public function getIdentities()
    {
        return [self::lof_sendgrid_addressbook . '_' . $this->getId()];
    }
}
