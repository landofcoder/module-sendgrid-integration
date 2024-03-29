<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\SendGrid\Model\Data;

use Lof\SendGrid\Api\Data\SingleSendInterface;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Model\Data
 */
class SingleSend extends \Magento\Framework\Api\AbstractExtensibleObject implements SingleSendInterface
{

    /**
     * Get send_at
     * @return string|null
     */
    public function getSendAt()
    {
        return $this->_get(self::SEND_AT);
    }

    /**
     * Set send_at
     * @param string $send_at
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSendAt($send_at)
    {
        return $this->setData(self::SEND_AT, $send_at);
    }

    /**
     * Get sender_id
     * @return string|null
     */
    public function getSenderId()
    {
        return $this->_get(self::SENDER_ID);
    }

    /**
     * Set sender_id
     * @param string $sender_id
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSenderId($sender_id)
    {
        return $this->setData(self::SENDER_ID, $sender_id);
    }

    /**
     * Get list_ids
     * @return string|null
     */
    public function getListIds()
    {
        return $this->_get(self::LIST_IDS);
    }

    /**
     * Set list_ids
     * @param string $list_ids
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setListIds($list_ids)
    {
        return $this->setData(self::LIST_IDS, $list_ids);
    }

    /**
     * Get suppression_group_id
     * @return string|null
     */
    public function getSuppressionGroupId()
    {
        return $this->_get(self::SUPPRESSION_GROUP_ID);
    }

    /**
     * Set suppression_group_id
     * @param string $suppression_group_id
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSuppressionGroupId($suppression_group_id)
    {
        return $this->setData(self::SUPPRESSION_GROUP_ID, $suppression_group_id);
    }

    /**
     * Get singlesend_id
     * @return string|null
     */
    public function getSinglesendId()
    {
        return $this->_get(self::SINGLESEND_ID);
    }

    /**
     * Set singlesend_id
     * @param string $singlesendId
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSinglesendId($singlesendId)
    {
        return $this->setData(self::SINGLESEND_ID, $singlesendId);
    }

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\SendGrid\Api\Data\SingleSendExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get create_date
     * @return string|null
     */
    public function getCreateDate()
    {
        return $this->_get(self::CREATE_DATE);
    }

    /**
     * Set create_date
     * @param string $createDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setCreateDate($createDate)
    {
        return $this->setData(self::CREATE_DATE, $createDate);
    }

    /**
     * Get update_date
     * @return string|null
     */
    public function getUpdateDate()
    {
        return $this->_get(self::UPDATE_DATE);
    }

    /**
     * Set update_date
     * @param string $updateDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setUpdateDate($updateDate)
    {
        return $this->setData(self::UPDATE_DATE, $updateDate);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get email_html
     * @return string|null
     */
    public function getEmailHtml()
    {
        return $this->_get(self::EMAIL_HTML);
    }

    /**
     * Set email_html
     * @param string $emailHtml
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setEmailHtml($emailHtml)
    {
        return $this->setData(self::EMAIL_HTML, $emailHtml);
    }
}
