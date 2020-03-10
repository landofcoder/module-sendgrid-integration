<?php
/**
 * Copyright (c) 2019  Landofcoder
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
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Backend\Model\View\Result\Redirect;

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
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param ManagerInterface $messageManager
     * @param SubscriberFactory $subscriberFactory
     * @param \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Data $helper,
        CollectionFactory $orderCollectionFactory,
        ManagerInterface $messageManager,
        SubscriberFactory $subscriberFactory,
        \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
    ) {
        $this->helper = $helper;
        $this->addressBookCollection = $addressBookCollection;
        $this->subscriberFactory= $subscriberFactory;
        $this->_messageManager = $messageManager;
        $this->addressBookCollection = $addressBookCollection;
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
        $api_key = $this->helper->getSendGridConfig('general', 'api_key');
        if ($this->helper->getSendGridConfig('general', 'add_customer') == 1) {
            $subscriber_list = $this->helper->getSendGridConfig('general', 'list_for_new_customer');
        } else {
            $subscriber_list = $this->helper->getSendGridConfig('general', 'subscribe_list');
        }
        $unsubscriber_list = $this->helper->getSendGridConfig('general', 'unsubscribe_list');
        $other_list = $this->helper->getSendGridConfig('general', 'other_group');
        $list_subscriber_id = '';
        $list = $this->helper->getAllList($curl, $api_key);
        foreach ($list as $items) {
            foreach ($items as $item) {
                if (isset($item->name)) {
                    if ($item->name == $subscriber_list) {
                        $list_subscriber_id = $item->id;
                    }
                }
            }
            break;
        }
        $list_unsubscriber = $this->helper->getUnsubscriberGroup($curl, $api_key);
        $unsubscriber_id = '';
        $other_list_id = '';
        foreach ($list_unsubscriber as $item) {
            if ($item->name == $unsubscriber_list) {
                $unsubscriber_id = $item->id;
            }
            if ($item->name == $other_list) {
                $other_list_id = $item->id;
            }
        }
        $addressBookCollection = $this->addressBookCollection->create()->addFieldToFilter('is_subscribed', '0');
        $list_other_email = '';
        foreach ($addressBookCollection as $addressBook) {
            if ($list_other_email == '') {
                $list_other_email .= "\"".$addressBook->getEmailAddress()."\"";
            } else {
                $list_other_email .= ",\"".$addressBook->getEmailAddress()."\"";
            }
        }
        $this->helper->syncUnsubscriber($curl, $api_key, $other_list_id, $list_other_email);
        $this->helper->syncSubscriber($curl, $api_key, $list_subscriber_id, $unsubscriber_id);
        $this->helper->syncSubscriberToM2($curl, $api_key, $list_subscriber_id);
        curl_close($curl);
    }
}
