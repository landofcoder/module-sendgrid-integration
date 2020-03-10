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
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Lof\SendGrid\Helper\Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\SendGrid\Helper\Data $helper,
        CollectionFactory $orderCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
    ) {
        $this->helper = $helper;
        $this->subscriberFactory= $subscriberFactory;
        $this->_messageManager = $messageManager;

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
        $list = $this->getAllList($curl, $api_key);
        $exist_subscriber = 0;
        $exist_customer = 0;
        $list_subscriber_id = '';
        $list_customer_id = '';
        foreach ($list as $items) {
            foreach ($items as $item) {
                if (isset($item->name)) {
                    if ($item->name == 'Subscribers') {
                        $exist_subscriber = 1;
                        $list_subscriber_id = $item->id;
                    }
                    if ($item->name == 'All Customers') {
                        $exist_customer = 1;
                        $list_customer_id = $item->id;
                    }
                }
            }
            break;
        }
        if ($exist_subscriber == 0) {
            $this->create_contact_list($curl, 'Subscribers', $api_key);
        }
        if ($exist_customer == 0) {
            $this->create_contact_list($curl, 'All Customers', $api_key);
        }
        $list2 = $this->getAllList($curl, $api_key);
        if ($list2) {
            foreach ($list2 as $items) {
                foreach ($items as $item) {
                    if (isset($item->name)) {
                        if ($item->name == 'Subscribers') {
                            $list_subscriber_id = $item->id;
                        }
                        if ($item->name == 'All Customers') {
                            $list_customer_id = $item->id;
                        }
                    }
                }
                break;
            }
        }
        $this->syncCustomers($curl, $api_key, $list_customer_id);
        $this->syncOrders($curl, $api_key, $list_customer_id);
        $this->syncSubscriber($curl, $api_key, $list_subscriber_id);
        $this->syncSubscriberToM2($curl, $api_key, $list_subscriber_id);

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
        curl_close($curl);
    }
    public function sync($curl, $contact, $list_id, $api_key)
    {
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => "{\"list_ids\": [\" $list_id  \"],\"contacts\":[".$contact."]}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key",
                "content-type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if ($err) {
            $this->_messageManager->addErrorMessage(__($err));
        } else {
            if (strpos($response, 'job_id')) {
                $this->_messageManager->addSuccessMessage(__("Contacts have been synced"));
            } else {
                $this->_messageManager->addErrorMessage(__("Something went wrong. Can't sync contacts"));
            }
        }
    }
    public function create_contact_list($curl, $name, $api_key)
    {
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/lists",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"name\":\"$name\"}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
    }
    public function getAllList($curl, $api_key)
    {
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/lists?page_size=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if ($err) {
            return '';
        } else {
            return json_decode($response, false);
        }
    }
    public function getContacts($curl, $api_key)
    {
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key"
            ),
        ));

        $response = curl_exec($curl);
        return json_decode($response, false);
    }
    public function syncSubscriberToM2($curl, $api_key, $list_subscriber_id)
    {
        $contacts = $this->getContacts($curl, $api_key);
        foreach ($contacts as $contact) {
            foreach ($contact as $item) {
                if (isset($item->list_ids)) {
                    if (in_array($list_subscriber_id, $item->list_ids)) {
                        $subscriber = $this->subscriberFactory->create();
                        $existing = $subscriber->getCollection()->addFieldToFilter("subscriber_email", $item->email)->getData();
                        if (count($existing) == 0) {
                            $subscriber->setSubscriberEmail($item->email)->setCustomerFirstname($item->first_name)->setCustomerLastname($item->last_name)->setStatus('1');
                            $subscriber->save();
                        }
                    }
                }
            }
            break;
        }
    }
    public function syncCustomers($curl, $api_key, $list_customer_id)
    {
        $customer_collection = $this->helper->getCustomerCollection();
        $contact = '';
        if (count($customer_collection) > 0) {
            foreach ($customer_collection as $key => $customer) {
                $arr = '{"email":'."\"".$customer->getEmail()."\"".',"first_name":'."\"".$customer->getFirstname()."\"".',"last_name":'."\"".$customer->getLastname()."\"".'}';
                if ($key == 1) {
                    $contact .= $arr;
                } else {
                    $contact .= ','.$arr;
                }
            }
            $this->sync($curl, $contact, $list_customer_id, $api_key);
        }
    }
    public function syncOrders($curl, $api_key, $list_customer_id)
    {
        $contact_order = '';
        $order_collection = $this->_orderCollectionFactory->create()->addFieldToSelect('customer_email')->addFieldToSelect('customer_firstname')->addFieldToSelect('customer_lastname');
        if (count($order_collection) > 0) {
            foreach ($order_collection as $key => $order) {
                $arr = '{"email":'."\"".$order->getCustomerEmail()."\"".',"first_name":'."\"".$order->getCustomerFirstname()."\"".',"last_name":'."\"".$order->getCustomerLastname()."\"".'}';
                if ($contact_order == '') {
                    $contact_order .= $arr;
                } else {
                    $contact_order .= ','.$arr;
                }
            }
            $this->sync($curl, $contact_order, $list_customer_id, $api_key);
        }
    }
    public function syncSubscriber($curl, $api_key, $list_subscriber_id)
    {
        $sub = $this->_subcriberCollectionFactory->create();
        if ($this->helper->getSendGridConfig('general', 'add_customer') == 1) {
            $sub->addFieldToFilter('subscriber_status', '1');
        }
        $subscriber = '';
        if (count($sub)) {
            foreach ($sub as $item) {
                $arr = '{"email":'."\"".$item->getSubscriberEmail()."\"".',"first_name":'."\"".$item->getCustomerFirstname()."\"".',"last_name":'."\"".$item->getCustomerLastname()."\"".'}';
                if ($subscriber == '') {
                    $subscriber .= $arr;
                } else {
                    $subscriber .= ','.$arr;
                }
            }
            $this->sync($curl, $subscriber, $list_subscriber_id, $api_key);
        }
    }
}
