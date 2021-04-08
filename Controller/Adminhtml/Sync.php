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

namespace Lof\SendGrid\Controller\Adminhtml;

use Exception;
use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\AddressBookFactory;
use Lof\SendGrid\Model\SenderFactory;
use Lof\SendGrid\Model\SingleSendFactory;
use Lof\SendGrid\Model\SubscriberFactory;
use Lof\SendGrid\Model\UnSubscriberFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class Sync
 *
 * @package Lof\SendGrid\Controller\Adminhtml
 */
abstract class Sync extends \Magento\Backend\App\Action
{
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var DateTimeFactory
     */
    public $_dateFactory;
    /**
     * @var \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory
     */
    public $addressBookCollection;
    /**
     * @var SingleSendFactory
     */
    public $singlesend;
    /**
     * @var SenderFactory
     */
    public $_sender;
    /**
     * @var ManagerInterface
     */
    public $_messageManager;
    /**
     * @var UnSubscriberFactory
     */
    public $_unsubscriber;
    /**
     * @var AddressBookFactory
     */
    public $addressbook;
    /**
     * @var CollectionFactory
     */
    public $_orderCollectionFactory;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    public $_subcriberCollectionFactory;
    /**
     * @var SubscriberFactory
     */
    public $_subscriber;

    /**
     * Sync constructor.
     * @param Context $context
     * @param Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param ManagerInterface $messageManager
     * @param \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection
     * @param SingleSendFactory $singleSendFactory
     * @param SenderFactory $senderFactory
     * @param DateTimeFactory $dateFactory
     * @param AddressBookFactory $addressBookFactory
     * @param SubscriberFactory $subscriber
     * @param UnSubscriberFactory $unsubscriber
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        CollectionFactory $orderCollectionFactory,
        ManagerInterface $messageManager,
        \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection,
        SingleSendFactory $singleSendFactory,
        SenderFactory $senderFactory,
        DateTimeFactory $dateFactory,
        AddressBookFactory $addressBookFactory,
        SubscriberFactory $subscriber,
        UnSubscriberFactory $unsubscriber,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
    ) {
        $this->helper = $helper;
        $this->_dateFactory = $dateFactory;
        $this->addressBookCollection = $addressBookCollection;
        $this->singlesend = $singleSendFactory;
        $this->_sender = $senderFactory;
        $this->_subscriber = $subscriber;
        $this->_unsubscriber = $unsubscriber;
        $this->_messageManager = $messageManager;
        $this->addressBookCollection = $addressBookCollection;
        $this->addressbook = $addressBookFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_subcriberCollectionFactory = $subcriberCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function SyncSingleSend()
    {
        $singleSends = $this->helper->getAllSingleSend();
        if (!isset($singleSends->result)) {
            $this->_messageManager->addErrorMessage(__("Some thing went wrong. May be wrong Api key"));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
        }
        $items = $singleSends->result;
        $singleSendIds = [];
        foreach ($items as $item) {
            if (!isset($item->id)) {
                continue;
            }
            $singleSendIds[] = $item->id;
            $model = $this->singlesend->create();
            $data = $this->helper->getDataSinglesend($item->id);
            $existing = $model->getCollection()->addFieldToFilter("singlesend_id", $item->id)->getData();
            if (count($existing)) {
                $entity_id = $existing[0]['entity_id'];
                $model->load($entity_id);
            }
            if (isset($item->id)) {
                $model->setSinglesendId($item->id);
            }
            if (isset($item->name)) {
                $model->setName($item->name);
            }
            if (isset($item->updated_at)) {
                $new_date_format = date('Y-m-d H:i:s', strtotime($item->updated_at));
                $model->setUpdateDate($new_date_format);
            }
            if (isset($item->created_at)) {
                $new_date_format = date('Y-m-d H:i:s', strtotime($item->created_at));
                $model->setCreateDate($new_date_format);
            }
            if (isset($item->status)) {
                $model->setStatus($item->status);
            }
            if (isset($data->send_at)) {
                $new_date_format = date('Y-m-d H:i:s', strtotime($item->send_at));
                $model->setSendAt($new_date_format);
            }
            if (isset($data->email_config->sender_id)) {
                $model->setSenderId($data->email_config->sender_id);
            }
            if (isset($data->email_config->suppression_group_id)) {
                $model->setSuppressionGroupId($data->email_config->suppression_group_id);
            }
            if (isset($data->send_to->list_ids)) {
                $model->setListIds(json_encode($data->send_to->list_ids));
            }
            if (isset($data->email_config->subject)) {
                $model->setSubject($data->email_config->subject);
            }
            if (isset($data->email_config->html_content)) {
                $model->setHtmlContent($data->email_config->html_content);
            }
            if (isset($data->email_config->plain_content)) {
                $model->setPlainContent($data->email_config->plain_content);
            }
            if (isset($data->email_config->editor)) {
                $model->setEditor($data->email_config->editor);
            }
            $model->save();
        }

        $singleSendCollectionDelete = $this->singlesend->create()->getCollection();
        if ($singleSendIds) {
            $singleSendCollectionDelete->addFieldToFilter('singlesend_id', ['nin' => $singleSendIds]);
        }
        $singleSendCollectionDelete->walk('delete');
    }

    /**
     *
     */
    public function SyncSender()
    {
        $senders = $this->helper->getAllSenders();
        $senderIds = [];
        foreach ($senders as $sender) {
            $model = $this->_sender->create();
            if (!isset($sender->id)) {
                continue;
            }
            $senderIds[] = $sender->id;
            $exits = $model->getCollection()->addFieldToFilter('sender_id', $sender->id)->getData();
            if (count($exits)) {
                $model->load($exits['0']['id']);
            }
            $model->setSenderId($sender->id);
            if (isset($sender->nickname)) {
                $model->setNickName($sender->nickname);
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
            try {
                $model->save();
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e);
            }
        }
        $senderCollectionDelete = $this->_sender->create()->getCollection();
        if ($senderIds) {
            $senderCollectionDelete->addFieldToFilter('sender_id', ['nin' => $senderIds]);
        }
        $senderCollectionDelete->walk('delete');
    }

    /**
     * @throws Exception
     */
    public function SyncContact()
    {
        $subscriber_list = $this->helper->getSendGridConfig('general', 'subscribe_list');

        $unsubscriber_list = $this->helper->getSendGridConfig('general', 'unsubscribe_list');
        $addNewCustomerToSubscriberList = $this->helper->getSendGridConfig('general', 'add_customer');
        if ($addNewCustomerToSubscriberList) {
            $other_list = $this->helper->getSendGridConfig('general', 'list_for_new_customer');
        } else {
            $other_list = $this->helper->getSendGridConfig('general', 'other_group');
        }
        $list_subscriber_id = '';
        $list = $this->helper->getAllList();
        if (!isset($list->result) && !isset($list['result'])) {
            $this->_messageManager->addErrorMessage(__("Some thing went wrong. May be wrong Api key"));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
        }
        $items = isset($list->result) ? $list->result : $list['result'];
        $other_list_id = '';
        foreach ($items as $item) {
            if (isset($item->id) && $item->id == $subscriber_list) {
                $list_subscriber_id = $item->id;
            }
            if ($addNewCustomerToSubscriberList && $item->id == $other_list) {
                $other_list_id = $item->id;
            }
        }
        $list_unsubscriber = $this->helper->getUnsubscriberGroup();
        $unsubscriber_id = '';
        foreach ($list_unsubscriber as $item) {
            if (isset($item->id)) {
                if ($item->id == $unsubscriber_list) {
                    $unsubscriber_id = $item->id;
                }
                if (!$addNewCustomerToSubscriberList && $item->id == $other_list) {
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
        if (!$addNewCustomerToSubscriberList) {
            foreach ($addressBookCollection as $addressBook) {
                if ($list_other_email == '') {
                    $list_other_email .= "\"".$addressBook->getEmailAddress()."\"";
                } else {
                    $list_other_email .= ",\"".$addressBook->getEmailAddress()."\"";
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
        } else {
            foreach ($addressBookCollection as $addressBook) {
                $arr = '{"email":'."\"".$addressBook->getEmailAddress()."\"".',"first_name":'."\"".$addressBook->getFirstname()."\"".',"last_name":'."\"".$addressBook->getLastname()."\"".'}';
                if ($list_other_email == '') {
                    $list_other_email .= $arr;
                } else {
                    $list_other_email .= ','.$arr;
                }
            }
            if ($list_other_email != '') {
                $response = $this->helper->sync($list_other_email, $other_list_id);
                if ($response) {
                    foreach ($addressBookCollection as $addressBook) {
                        $addressBook->setIsSynced('1');
                        $addressBook->save();
                    }
                }
            }
        }

        $this->helper->syncSubscriber($list_subscriber_id, $unsubscriber_id);
//        $this->helper->syncSubscriberToM2($list_subscriber_id);
    }

    /**
     *
     */
    public function moveCustomerToSubscriberGroup()
    {
        $group = $this->helper->getSendGridConfig('general', 'other_group');
        $customerCollection = $this->helper->getCustomerCollection();
        foreach ($customerCollection as $customer) {
            $subscriberCollection = $this->_subcriberCollectionFactory->create();
            $exist = $subscriberCollection->addFieldToFilter('subscriber_email', $customer->getEmail())->getData();
            $addressbookCollection = $this->addressbook->create()->getCollection();
            $existOnThis = $addressbookCollection->addFieldToFilter('email_address', $customer->getEmail())->getData();
            if (!count($exist) && !count($existOnThis)) {
                if (!count($existOnThis)) {
                    $addressbook = $this->addressbook->create();
                } else {
                    $entity_id = $existOnThis['0']['id'];
                    $addressbook = $this->addressbook->create()->load($entity_id);
                }
                $addressbook->setEmailAddress($customer->getEmail())
                    ->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setSourceFrom('Customer')
                    ->setCustomerId($customer->getId())
                    ->setIsSubscribed('0')
                    ->setCreatedAt($this->_dateFactory->create()->gmtDate())
                    ->setIsSync('0')
                    ->setGroupId($group);
                $addressbook->save();
            }
        }
        $orderCollection = $this->_orderCollectionFactory->create();
        foreach ($orderCollection as $order) {
            $subscriberCollection = $this->_subcriberCollectionFactory->create();
            $exist = $subscriberCollection->addFieldToFilter('subscriber_email', $order->getCustomerEmail())->getData();
            $addressbookCollection = $this->addressbook->create()->getCollection();
            $existOnThis = $addressbookCollection->addFieldToFilter('email_address', $order->getCustomerEmail())
                ->getData();
            if (!count($exist)) {
                if (!count($existOnThis)) {
                    $addressbook = $this->addressbook->create();
                } else {
                    $entity_id = $existOnThis['0']['id'];
                    $addressbook = $this->addressbook->create()->load($entity_id);
                }
                $addressbook->setEmailAddress($order->getCustomerEmail())
                    ->setFirstname($order->getCustomerFirstname())
                    ->setLastname($order->getCustomerLastname())
                    ->setSourceFrom('Order')
                    ->setCustomerId($order->getCustomerId())
                    ->setOrderId($order->getId())
                    ->setIsSubscribed('0')
                    ->setCreatedAt($this->_dateFactory->create()->gmtDate())
                    ->setIsSync('0')
                    ->setGroupId($group);
                $addressbook->save();
            }
        }
    }
}
