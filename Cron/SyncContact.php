<?php
/**
 * LandOfCoder
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
 * @category   LandOfCoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2020 Landofcoder (http://www.LandOfCoder.com/)
 * @license    http://www.LandOfCoder.com/LICENSE-1.0.html
 */

namespace Lof\SendGrid\Cron;

use Lof\SendGrid\Helper\Data;
use Magento\Framework\Controller\ResultInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Lof\SendGrid\Model\UnSubscriberFactory;


/**
 * Class SyncContact
 *
 * @package Lof\SendGrid\Cron
 */
class SyncContact
{
    protected $helper;
    /**
     * @var \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory
     */
    private $addressBookCollection;
    /**
     * @var UnSubscriberFactory
     */
    private UnSubscriberFactory $_unsubscriber;
    /**
     * @var \Lof\SendGrid\Model\SubscriberFactory
     */
    private  $_subscriber;

    public function __construct(
        Data $helper,
        \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection,
        \Lof\SendGrid\Model\SubscriberFactory $subscriber,
        UnSubscriberFactory $unsubscriber
    ) {
        $this->helper = $helper;
        $this->addressBookCollection = $addressBookCollection;
        $this->_subscriber = $subscriber;
        $this->_unsubscriber = $unsubscriber;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $cron_enable = $this->helper->getSendGridConfig('sync', 'cron_enable');
        if ($cron_enable) {
            $curl = curl_init();
            $token = $this->helper->getSendGridConfig('general', 'api_key');
            if ($this->helper->getSendGridConfig('general', 'add_customer')) {
                $subscriber_list = $this->helper->getSendGridConfig('general', 'list_for_new_customer');
            } else {
                $subscriber_list = $this->helper->getSendGridConfig('general', 'subscribe_list');
            }
            $unsubscriber_list = $this->helper->getSendGridConfig('general', 'unsubscribe_list');
            $other_list = $this->helper->getSendGridConfig('general', 'other_group');
            $list_subscriber_id = '';
            $list = $this->helper->getAllList($curl, $token);
            $items = get_object_vars($list)['result'];
            foreach ($items as $item) {
                if (isset($item->name) && $item->name == $subscriber_list) {
                    $list_subscriber_id = $item->id;
                }
            }
            $list_unsubscriber = $this->helper->getUnsubscriberGroup($curl, $token);
            $unsubscriber_id = '';
            $other_list_id = '';
            foreach ($list_unsubscriber as $item) {
                if (isset($item->name)) {
                    if ($item->name == $unsubscriber_list) {
                        $unsubscriber_id = $item->id;
                    }
                    if ($item->name == $other_list) {
                        $other_list_id = $item->id;
                    }
                }
            }

            $this->helper->syncSubscriber($curl, $token, $list_subscriber_id, $unsubscriber_id);
            $this->helper->syncSubscriberToM2($curl, $token, $list_subscriber_id);

            $subscribers_groups = $this->helper->getAllList($curl, $token);
            $subscribers_groups = get_object_vars($subscribers_groups)['result'];
            foreach ($subscribers_groups as $subscribers_group) {
                $model = $this->_subscriber->create();
                $exits = $model->getCollection()->addFieldToFilter('subscriber_group_id', $subscribers_group->id)->getData();
                if (count($exits)) {
                    $model->load($exits['0']['id']);
                }
                $model->setSubscriberGroupId($subscribers_group->id)
                    ->setSubscriberGroupName($subscribers_group->name)
                    ->setSubscriberCount($subscribers_group->contact_count);
                $model->save();
            }
            $unsubscribers_groups = $this->helper->getUnsubscriberGroup($curl, $token);
            foreach ($unsubscribers_groups as $unsubscribers_group) {
                $model = $this->_unsubscriber->create();
                $exits = $model->getCollection()->addFieldToFilter('unsubscriber_group_id', $unsubscribers_group->id)->getData();
                if (count($exits)) {
                    $model->load($exits['0']['id']);
                }
                $model->setUnsubscriberGroupId($unsubscribers_group->id)
                    ->setUnsubscriberGroupName($unsubscribers_group->name)
                    ->setUnsubscriberCount($unsubscribers_group->unsubscribes);
                $model->save();
            }

            //sync address book (contact)
            $addressBookCollection = $this->addressBookCollection->create()
                ->addFieldToFilter('is_subscribed', '0')
                ->addFieldToFilter('is_synced', '0');
            $list_other_email = '';
            foreach ($addressBookCollection as $addressBook) {
                if ($list_other_email == '') {
                    $list_other_email .= "\"".$addressBook->getEmailAddress()."\"";
                } else {
                    $list_other_email .= ",\"".$addressBook->getEmailAddress()."\"";
                }
            }
            if ($list_other_email != '') {
                $response = $this->helper->syncUnsubscriber($curl, $token, $other_list_id, $list_other_email);
                if (isset($response->recipient_emails)) {
                    foreach ($addressBookCollection as $addressBook) {
                        $addressBook->setIsSynced('1');
                        $addressBook->save();
                    }
                }
            }
            curl_close($curl);
        }
    }
}
