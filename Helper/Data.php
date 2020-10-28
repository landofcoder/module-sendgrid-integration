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
namespace Lof\SendGrid\Helper;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Message\ManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SENDGRID = 'sendgrid/';
    /**
     * @var ManagerInterface
     */
    private $_messageManager;
    /**
     * @var CollectionFactory
     */
    private $_customerFactory;
    /**
     * @var SubscriberFactory
     */
    private $_subscriberFactory;

    public function __construct(
        CollectionFactory $customerFactory,
        ScopeConfigInterface $scopeConfig,
        SubscriberFactory $subscriberFactory,
        ManagerInterface $manager
    ) {
        $this->_customerFactory = $customerFactory;
        $this->scopeConfig=$scopeConfig;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_messageManager = $manager;
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

    public function getAllSingleSend($token)
    {
        $httpHeaders = new \Zend\Http\Headers();
        $httpHeaders->addHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
        $request = new \Zend\Http\Request();
        $request->setHeaders($httpHeaders);
        $request->setUri('https://api.sendgrid.com/v3/marketing/singlesends');
        $request->setMethod(\Zend\Http\Request::METHOD_GET);
        $params = new \Zend\Stdlib\Parameters();
        $request->setQuery($params);
        $client = new \Zend\Http\Client();
        $options = [
            'adapter'   => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];
        $client->setOptions($options);
        $response = $client->send($request);
        $collection = ($response->getBody());
        return json_decode($collection, false);
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
    public function getAllList()
    {
        $api_key = $this->getSendGridConfig('general', 'api_key');
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
        $err = curl_error($curl);
        $response = curl_exec($curl);
        return json_decode($response, false);
    }
    public function getUnsubscriberGroup()
    {
        $api_key = $this->getSendGridConfig('general', 'api_key');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/asm/groups",
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
    public function syncSubscriberToM2($curl, $api_key, $list_subscriber_id)
    {
        $contacts = $this->getContacts($curl, $api_key);
        $items = get_object_vars($contacts)['result'];
        foreach ($items as $item) {
            if (isset($item->list_ids)) {
                if (in_array($list_subscriber_id, $item->list_ids)) {
                    $subscriber = $this->_subscriberFactory->create();
                    $existing = $subscriber->getCollection()->addFieldToFilter("subscriber_email", $item->email)->getData();
                    if (count($existing) == 0) {
                        $subscriber->setSubscriberEmail($item->email)->setCustomerFirstname($item->first_name)->setCustomerLastname($item->last_name)->setStatus('1');
                        $subscriber->save();
                    }
                }
            }
        }
    }
    public function syncSubscriber($curl, $api_key, $list_subscriber_id, $unsubscriber_list_id)
    {
        $sub = $this->_subscriberFactory->create()->getCollection();
        $unsub = $this->_subscriberFactory->create()->getCollection();
        if ($this->getSendGridConfig('general', 'add_customer') == 0) {
            $sub->addFieldToFilter('subscriber_status', '1');
            $unsub->addFieldToFilter('subscriber_status', '3');
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
            if (count($unsub)) {
                $arr2 = '';
                foreach ($unsub as $item) {
                    if ($arr2 == '') {
                        $arr2 .= "\"".$item->getSubscriberEmail()."\"";
                    } else {
                        $arr2 .= ",\"".$item->getSubscriberEmail()."\"";
                    }
                }
                $this->syncUnsubscriber($curl, $api_key, $unsubscriber_list_id, $arr2);
            }
        } else {
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
    public function syncUnsubscriber($curl, $api_key, $list_unsubscriber_id, $list_email)
    {
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/asm/groups/$list_unsubscriber_id/suppressions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"recipient_emails\":[$list_email]}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key",
                "content-type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        return json_decode($response);
    }
    public function getDataSinglesend($id, $token)
    {
//        $campaign_id = "test_url_param";
//
//        try {
//            $response = $sg->client->campaigns()->_($id)->get();
//            print $response->statusCode() . "\n";
//            print_r($response->headers());
//            print $response->body() . "\n";
//        } catch (Exception $e) {
//            echo 'Caught exception: ',  $e->getMessage(), "\n";
//        }
//        return json_decode($response, false);
    }

    public function deleteSingleSend($api_key, $singlesend_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends/$singlesend_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_POSTFIELDS => "{}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $response;
    }
    public function getAllSenders($api_key)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/senders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key",
                "content-type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
    public function schedule($api_key, $singlesendId, $date)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends/$singlesendId/schedule",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => "{\"send_at\":\"$date\"}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    }
    public function testAPI($api_key)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/scopes",
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
        if (isset(json_decode($response)->scopes)) {
            return true;
        } else {
            return false;
        }
    }

    public function createSingleSend($sg, $data)
    {
        $data = json_decode($data);
        try {
            $response = $sg->client->campaigns()->post($data);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    public function updateSingleSend($sg, $singleSendId, $data)
    {

        $query_params = json_decode('{"limit": 1, "offset": 1}');

        try {
            $response = $sg->client->campaigns()->get(null, $query_params);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        $request_body = json_decode($data);
        try {
            $response = $sg->client->campaigns()->_($singleSendId)->patch($request_body);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
}
