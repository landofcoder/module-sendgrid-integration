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
use Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory;
use Lof\SendGrid\Model\SubscriberFactory;
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
     * @var CollectionFactory
     */
    private $addressBookCollection;
    /**
     * @var UnSubscriberFactory
     */
    private $_unsubscriber;
    /**
     * @var SubscriberFactory
     */
    private $_subscriber;

    public function __construct(
        Data $helper,
        CollectionFactory $addressBookCollection,
        SubscriberFactory $subscriber,
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
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $cron_enable = $this->helper->getSendGridConfig('sync', 'cron_enable');
        if ($cron_enable) {
            if ($this->helper->getSendGridConfig('general', 'add_customer')) {
                $subscriber_list = $this->helper->getSendGridConfig('general', 'list_for_new_customer');
            } else {
                $subscriber_list = $this->helper->getSendGridConfig('general', 'subscribe_list');
            }
            $unsubscriber_list = $this->helper->getSendGridConfig('general', 'unsubscribe_list');
            $other_list = $this->helper->getSendGridConfig('general', 'other_group');
            $list_subscriber_id = '';
            $list = $this->helper->getAllList();
            if (!isset($list->result) && !isset($list['result'])) {
                $this->_messageManager->addErrorMessage(__("Some thing went wrong. May be wrong Api key"));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
            }
            $items = isset($list->result) ? $list->result : $list['result'];
            foreach ($items as $item) {
                if (isset($item->id) && $item->id == $subscriber_list) {
                    $list_subscriber_id = $item->id;
                }
            }
            $list_unsubscriber = $this->helper->getUnsubscriberGroup();
            $unsubscriber_id = '';
            $other_list_id = '';
            foreach ($list_unsubscriber as $item) {
                if (isset($item->id)) {
                    if ($item->id == $unsubscriber_list) {
                        $unsubscriber_id = $item->id;
                    }
                    if ($item->name == $other_list) {
                        $other_list_id = $item->id;
                    }
                }
            }

            //save subscriber group in sendGrid to m2, delete subscriber group in m2 and not in sendGrid
            $subscribers_groups = $items;
            $subIds = [];
            foreach ($subscribers_groups as $subscribers_group) {
                $subIds[] = $subscribers_group->id;
                $model = $this->_subscriber->create();
                $exits = $model->getCollection()
                    ->addFieldToFilter('subscriber_group_id', $subscribers_group->id)
                    ->getFirstItem();
                if ($exits->getId()) {
                    $model->load($exits->getId());
                }
                $model->setSubscriberGroupId($subscribers_group->id)
                    ->setSubscriberGroupName($subscribers_group->name)
                    ->setSubscriberCount($subscribers_group->contact_count);
                $model->save();
            }
            $subCollectionDelete = $this->_subscriber->create()
                ->getCollection();
            if ($subIds) {
                $subCollectionDelete->addFieldToFilter('subscriber_group_id', ['nin' => $subIds]);
            }
            $subCollectionDelete->walk('delete');

            //save unsubscriber group in sendGrid to m2, delete unsubscriber group in m2 and not in sendGrid
            $unsubIds = [];
            foreach ($list_unsubscriber as $unsubscribers_group) {
                $unsubIds[] = $unsubscribers_group->id;
                $model = $this->_unsubscriber->create();
                $exits = $model->getCollection()
                    ->addFieldToFilter('unsubscriber_group_id', $unsubscribers_group->id)
                    ->getFirstItem();
                if ($exits->getId()) {
                    $model->load($exits->getId());
                }
                $count = isset($unsubscribers_group->unsubscribes) ? $unsubscribers_group->unsubscribes : 0;
                $model->setUnsubscriberGroupId($unsubscribers_group->id)
                    ->setUnsubscriberGroupName($unsubscribers_group->name)
                    ->setUnsubscriberCount($count);
                $model->save();
            }
            $unsubCollectionDelete = $this->_unsubscriber->create()
                ->getCollection();
            if ($unsubIds) {
                $unsubCollectionDelete->addFieldToFilter('unsubscriber_group_id', ['nin' => $unsubIds]);
            }
            $unsubCollectionDelete->walk('delete');

            //sync address book (contact)
            $addressBookCollection = $this->addressBookCollection->create()
                ->addFieldToFilter('is_subscribed', '0')
                ->addFieldToFilter('is_synced', '0');
            $list_other_email = '';
            foreach ($addressBookCollection as $addressBook) {
                if ($list_other_email == '') {
                    $list_other_email .= "\"" . $addressBook->getEmailAddress() . "\"";
                } else {
                    $list_other_email .= ",\"" . $addressBook->getEmailAddress() . "\"";
                }
            }
            if ($list_other_email != '') {
                $response = $this->helper->syncUnsubscriber($other_list_id, $list_other_email);
                if (isset($response->recipient_emails)) {
                    foreach ($addressBookCollection as $addressBook) {
                        $addressBook->setIsSynced('1');
                        $addressBook->save();
                    }
                }
            }

            $this->helper->syncSubscriber($list_subscriber_id, $unsubscriber_id);
//            $this->helper->syncSubscriberToM2($list_subscriber_id);
        }
    }
}
