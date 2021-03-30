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
use Lof\SendGrid\Model\SingleSendFactory;
use Lof\SendGrid\Model\UnSubscriberFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Lof\SendGrid\Model\AddressBookFactory;

/**
 * Class SyncToNewDb
 *
 * @package Lof\SendGrid\Cron
 */
class SyncToNewDb
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var UnSubscriberFactory
     */
    private $_unsubscriber;
    /**
     * @var AddressBookFactory
     */
    private $addressbook;
    /**
     * @var CollectionFactory
     */
    private $_orderCollectionFactory;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    private $_subcriberCollectionFactory;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;

    /**
     * SyncToNewDb constructor.
     * @param Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param AddressBookFactory $addressBookFactory
     * @param UnSubscriberFactory $unsubscriber
     * @param DateTimeFactory $dateFactory
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
     */
    public function __construct(
        Data $helper,
        CollectionFactory $orderCollectionFactory,
        AddressBookFactory $addressBookFactory,
        UnSubscriberFactory $unsubscriber,
        DateTimeFactory $dateFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
    ) {
        $this->helper = $helper;
        $this->_unsubscriber = $unsubscriber;
        $this->addressbook = $addressBookFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_dateFactory = $dateFactory;
        $this->_subcriberCollectionFactory = $subcriberCollectionFactory;
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
        }
    }
}
