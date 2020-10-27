<?php
/**
 * Copyright (c) 2020  Landofcoder
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lof\SendGrid\Controller\Adminhtml\System\Config;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\SenderFactory;
use Lof\SendGrid\Model\SingleSendFactory;
use Lof\SendGrid\Model\UnSubscriberFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Lof\SendGrid\Model\AddressBookFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class Sync
 *
 * @package Lof\SendGrid\Controller\Adminhtml\System/Config
 */
class Sync extends \Magento\Backend\App\Action
{
    protected $helper;
    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    private $_subcriberCollectionFactory;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var ManagerInterface
     */
    private $_messageManager;
    /**
     * @var \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory
     */
    private $addressBookCollection;
    /**
     * @var SingleSendFactory
     */
    private $singlesend;
    /**
     * @var SenderFactory
     */
    private $_sender;
    /**
     * @var UnSubscriberFactory
     */
    private $_unsubscriber;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param ManagerInterface $messageManager
     * @param \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection
     * @param SingleSendFactory $singleSendFactory
     * @param SenderFactory $senderFactory
     * @param DateTimeFactory $dateFactory
     * @param AddressBookFactory $addressBookFactory
     * @param \Lof\SendGrid\Model\SubscriberFactory $subscriber
     * @param UnSubscriberFactory $unsubscriber
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Data $helper,
        CollectionFactory $orderCollectionFactory,
        ManagerInterface $messageManager,
        \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection,
        SingleSendFactory $singleSendFactory,
        SenderFactory $senderFactory,
        DateTimeFactory $dateFactory,
        AddressBookFactory $addressBookFactory,
        \Lof\SendGrid\Model\SubscriberFactory $subscriber,
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
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $curl = curl_init();
        $token = $this->helper->getSendGridConfig('general', 'api_key'); //get Api Key
        //get All Single Send
        $object = $this->helper->getAllSingleSend($token);
        if (isset($object->errors)) {
            $this->_messageManager->addErrorMessage(__("Some thing went wrong. May be wrong Api key"));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
        }
        $items = get_object_vars($object)['result'];
        foreach ($items as $item) {
            //save singlge send
            $model = $this->singlesend->create();
            $data = $this->helper->getDataSinglesend($item->id, $token);
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
                $model->setUpdateDate($item->updated_at);
            }
            if (isset($item->created_at)) {
                $model->setCreateDate($item->created_at);
            }
            if (isset($item->status)) {
                $model->setStatus($item->status);
            }
            if (isset($data->send_at)) {
                $model->setSendAt($data->send_at);
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

        // sync sender
        $senders = $this->helper->getAllSenders($token);
        foreach ($senders as $sender) {
            $model = $this->_sender->create();
            $exits = $model->getCollection()->addFieldToFilter('sender_id', $sender->id)->getData();
            if (count($exits)) {
                $model->load($exits['0']['id']);
            }
            $model->setSenderId($sender->id)
                ->setNickName($sender->nickname)
                ->setFrom($sender->from->email)
                ->setFromName($sender->from->name)
                ->setReplyTo($sender->reply_to->email)
                ->setAddress($sender->address)
                ->setCity($sender->city)
                ->setCountry($sender->country)
                ->setVerified($sender->verified->status)
                ->setUpdateAt($sender->updated_at)
                ->setCreateAt($sender->created_at);
            $model->save();
        }

        //sync sunscriber and unsubscriber groups
        if ($this->helper->getSendGridConfig('general', 'add_customer') == 1) {
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
            if (isset($item->name)) {
                if ($item->name == $subscriber_list) {
                    $list_subscriber_id = $item->id;
                }
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
        $subscribers_groups = $this->helper->getAllList();
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
        $unsubscribers_groups = $this->helper->getUnsubscriberGroup();
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
                $addressbook->setEmailAddress($customer->getEmail())->setFirstname($customer->getFirstname())->setLastname($customer->getLastname())->setSourceFrom('Customer')->setCustomerId($customer->getId())->setIsSubscribed('0')->setCreatedAt($this->_dateFactory->create()->gmtDate())->setIsSync('0')->setGroupId($group);
                $addressbook->save();
            }
        }
        $orderCollection = $this->_orderCollectionFactory->create();
        foreach ($orderCollection as $order) {
            $subscriberCollection = $this->_subcriberCollectionFactory->create();
            $exist = $subscriberCollection->addFieldToFilter('subscriber_email', $order->getCustomerEmail())->getData();
            $addressbookCollection = $this->addressbook->create()->getCollection();
            $existOnThis = $addressbookCollection->addFieldToFilter('email_address', $order->getCustomerEmail())->getData();
            if (!count($exist)) {
                if (!count($existOnThis)) {
                    $addressbook = $this->addressbook->create();
                } else {
                    $entity_id = $existOnThis['0']['id'];
                    $addressbook = $this->addressbook->create()->load($entity_id);
                }
                $addressbook->setEmailAddress($order->getCustomerEmail())->setFirstname($order->getCustomerFirstname())->setLastname($order->getCustomerLastname())->setSourceFrom('Order')->setCustomerId($order->getCustomerId())->setOrderId($order->getId())->setIsSubscribed('0')->setCreatedAt($this->_dateFactory->create()->gmtDate())->setIsSync('0')->setGroupId($group);
                $addressbook->save();
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
    }
}
