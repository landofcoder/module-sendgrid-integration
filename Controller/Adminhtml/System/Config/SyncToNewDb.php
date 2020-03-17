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
 * @package Lof\SendGrid\Controller\Adminhtml\System/Config
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
     * @var DateTimeFactory
     */
    private $_dateFactory;

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
                $addressbook->setEmailAddress($order->getCustomerEmail())->setFirstname($order->getCustomerFirstname())->setLastname($order->getCustomerLastname())->setSourceFrom('Order')->setCustomerId($order->getCustomerId())->setOrderId($order->getId())->setIsSubscribed('0')->setCreatedAt($this->_dateFactory->create()->gmtDate())->setIsSync('0')->setGroupId($group);
                $addressbook->save();
            } elseif (count($exist) == 0) {
                $entity_id = $existOnThis['0']['id'];
                $addressbook = $this->addressbook->create()->load($entity_id);
                $addressbook->setEmailAddress($order->getCustomerEmail())->setFirstname($order->getCustomerFirstname())->setLastname($order->getCustomerLastname())->setSourceFrom('Order')->setCustomerId($order->getCustomerId())->setOrderId($order->getId())->setIsSubscribed('0')->setCreatedAt($this->_dateFactory->create()->gmtDate())->setIsSync('0')->setGroupId($group);
                $addressbook->save();
            }
        }
    }
}
