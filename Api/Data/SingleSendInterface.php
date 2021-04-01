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

namespace Lof\SendGrid\Api\Data;

/**
 * Interface SingleSendInterface
 *
 * @package Lof\SendGrid\Api\Data
 */
interface SingleSendInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const CREATE_DATE = 'create_date';
    const NAME = 'name';
    const STATUS = 'status';
    const SINGLESEND_ID = 'singlesend_id';
    const UPDATE_DATE = 'update_date';
    const EMAIL_HTML = 'email_html';
    const SEND_AT = 'send_at';
    const SENDER_ID = 'sender_id';
    const SUPPRESSION_GROUP_ID = 'suppression_group_id';
    const LIST_IDS = 'list_ids';

    /**
     * Get singlesend_id
     * @return string|null
     */
    public function getSinglesendId();

    /**
     * Set singlesend_id
     * @param string $singlesendId
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSinglesendId($singlesendId);

    /**
    * Get send_at
    * @return string|null
    */
    public function getSendAt();

    /**
     * Set send_at
     * @param string $send_at
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSendAt($send_at);

    /**
     * Get sender_id
     * @return string|null
     */
    public function getSenderId();

    /**
     * Set sender_id
     * @param string $sender_id
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSenderId($sender_id);

    /**
     * Get list_ids
     * @return string|null
     */
    public function getListIds();

    /**
     * Set list_ids
     * @param string $list_ids
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setListIds($list_ids);

    /**
    * Get suppression_group_id
    * @return string|null
    */
    public function getSuppressionGroupId();

    /**
     * Set suppression_group_id
     * @param string $suppression_group_id
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSuppressionGroupId($suppression_group_id);



    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setEntityId($entityId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\SendGrid\Api\Data\SingleSendExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
    );

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setName($name);

    /**
     * Get create_date
     * @return string|null
     */
    public function getCreateDate();

    /**
     * Set create_date
     * @param string $createDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setCreateDate($createDate);

    /**
     * Get update_date
     * @return string|null
     */
    public function getUpdateDate();

    /**
     * Set update_date
     * @param string $updateDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setUpdateDate($updateDate);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setStatus($status);

    /**
     * Get email_html
     * @return string|null
     */
    public function getEmailHtml();

    /**
     * Set email_html
     * @param string $emailHtml
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setEmailHtml($emailHtml);
}
