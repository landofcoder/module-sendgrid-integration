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

namespace Lof\SendGrid\Cron;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\SenderFactory;

/**
 * Class SyncSender
 *
 * @package Lof\SendGrid\Cron
 */
class SyncSender
{
    /**
     * @var SenderFactory
     */
    private $_sender;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper,
        SenderFactory $senderFactory
    ) {
        $this->helper = $helper;
        $this->_sender = $senderFactory;
    }

    /**
     * Execute view action
     *
     * @return void
     */
    public function execute()
    {
        $cron_enable = $this->helper->getSendGridConfig('sync', 'cron_enable');
        if ($cron_enable) {
            $senders = $this->helper->getAllSenders();
            foreach ($senders as $sender) {
                $model = $this->_sender->create();
                if (!isset($sender->id)) {
                    continue;
                }
                $exits = $model->getCollection()->addFieldToFilter('sender_id', $sender->id)->getData();
                if (count($exits)) {
                    $model->load($exits['0']['id']);
                }
                $model->setSenderId($sender->id);
                if (isset($item->nickname)) {
                    $model->setNickName($item->nickname);
                }
                if (isset($sender->from->email)) {
                    $model->setFrom($sender->from->email);
                }
                if (isset($sender->from->name)) {
                    $model->setFromName($sender->from->name);
                }
                if (isset($sender->reply_to->email)) {
                    $model->setReplyTo($sender->reply_to->email);
                }
                if (isset($sender->address)) {
                    $model->setAddress($sender->address);
                }
                if (isset($sender->city)) {
                    $model->setCity($sender->city);
                }
                if (isset($sender->country)) {
                    $model->setCountry($sender->country);
                }
                if (isset($sender->verified->status)) {
                    $model->setVerified($sender->verified->status);
                }
                if (isset($sender->updated_at)) {
                    $model->setUpdateAt($sender->updated_at);
                }
                if (isset($sender->created_at)) {
                    $model->setCreateAt($sender->created_at);
                }
                $model->save();
            }
        }
    }
}
