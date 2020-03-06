<?php
namespace Lof\SendGrid\Helper;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SENDGRID = 'sendgrid/';
    public function __construct(
        CollectionFactory $customerFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_customerFactory = $customerFactory;
        $this->scopeConfig=$scopeConfig;
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    public function getSendGridConfig($group, $code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SENDGRID .$group.'/'. $code, $storeId);
    }
    public function getSingleSend()
    {
        $curl = curl_init();
        $api_key = $this->getSendGridConfig('general', 'api_key');
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends",
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

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }
    public function getCustomerCollection()
    {
        return $this->_customerFactory->create();
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
    public function getAllList()
    {
        $api_key = $this->getSendGridConfig('general','api_key');
        $curl = curl_init();
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
