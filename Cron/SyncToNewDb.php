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

use Exception;
use Lof\SendGrid\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Lof\SendGrid\Model\AddressBookFactory;

/**
 * Class SyncToNewDb
 *
 * @package Lof\SendGrid\Cron
 */
class SyncToNewDb extends \Magento\Backend\App\Action
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
     * @var AddressBookFactory
     */
    private $addressbook;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param AddressBookFactory $addressBookFactory
     * @param DateTimeFactory $dateFactory
     * @param ManagerInterface $messageManager
     * @param SubscriberFactory $subscriberFactory
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        CollectionFactory $orderCollectionFactory,
        AddressBookFactory $addressBookFactory,
        DateTimeFactory $dateFactory,
        ManagerInterface $messageManager,
        SubscriberFactory $subscriberFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
    ) {
        $this->helper = $helper;
        $this->addressbook = $addressBookFactory;
        $this->subscriberFactory= $subscriberFactory;
        $this->_dateFactory = $dateFactory;
        $this->_messageManager = $messageManager;

        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_subcriberCollectionFactory = $subcriberCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $group = $this->helper->getSendGridConfig('general', 'other_group');
        $customerCollection = $this->helper->getCustomerCollection();
        foreach ($customerCollection as $customer) {
            $subscriberCollection = $this->_subcriberCollectionFactory->create();
            $exist = $subscriberCollection->addFieldToFilter('subscriber_email', $customer->getEmail())->getData();
            $addressbookCollection = $this->addressbook->create()->getCollection();
            $existOnThis = $addressbookCollection->addFieldToFilter('email_address', $customer->getEmail())->getData();
            if ((count($exist) == 0) && (count($existOnThis) == 0)) {
                $addressbook = $this->addressbook->create();
                $addressbook->setEmailAddress($customer->getEmail())->setFirstname($customer->getFirstname())->setLastname($customer->getLastname())->setSourceFrom('Customer')->setCustomerId($customer->getId())->setIsSubscribed('0')->setCreatedAt($this->_dateFactory->create()->gmtDate())->setIsSync('0')->setGroupId($group);
                $addressbook->save();
            } elseif (count($exist) == 0) {
                $entity_id = $existOnThis['0']['id'];
                $addressbook = $this->addressbook->create()->load($entity_id);
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
            if ((count($exist) == 0) && (count($existOnThis) == 0)) {
                $addressbook = $this->addressbook->create();
                $addressbook->setEmailAddress($order->getCustomerEmail())->setFirstname($order->getCustomerFirstname())->setLastname($order->getCustomerLastname())->setSourceFrom('Order')->setCustomerId($order->getCustomerId())->setOrderId($order->getId())->setIsSubscribed('0')->setIsSync('0')->setGroupId($group);
                $addressbook->save();
            } elseif (count($exist) == 0) {
                $entity_id = $existOnThis['0']['id'];
                $addressbook = $this->addressbook->create()->load($entity_id);
                $addressbook->setEmailAddress($order->getCustomerEmail())->setFirstname($order->getCustomerFirstname())->setLastname($order->getCustomerLastname())->setSourceFrom('Order')->setCustomerId($order->getCustomerId())->setOrderId($order->getId())->setIsSubscribed('0')->setIsSync('0')->setGroupId($group);
                $addressbook->save();
            }
        }
    }
}
