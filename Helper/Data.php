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

/**
 * Class Data
 * @package Lof\SendGrid\Helper
 */
class Data extends AbstractHelper
{
    /**
     *
     */
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

    /**
     * Data constructor.
     * @param CollectionFactory $customerFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param SubscriberFactory $subscriberFactory
     * @param ManagerInterface $manager
     */
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

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $group
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getSendGridConfig($group, $code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SENDGRID .$group.'/'. $code, $storeId);
    }

    /**
     * @param $token
     * @return mixed
     */
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

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCustomerCollection()
    {
        return $this->_customerFactory->create();
    }

    /**
     * @param $curl
     * @param $contact
     * @param $list_id
     */
    public function sync($contact, $list_id)
    {
        $url = "https://api.sendgrid.com/v3/marketing/contacts";
        $type = "PUT";
        $data = "{\"list_ids\": [\"$list_id\"],\"contacts\":[".$contact."]}";
        $response = $this->sendRestApi($url, $type, $data);
        if (strpos($response, 'job_id')) {
            $this->_messageManager->addSuccessMessage(__("Contacts have been synced"));
        } else {
            $this->_messageManager->addErrorMessage(__("Something went wrong. Can't sync contacts"));
        }
    }


    public function getAllList()
    {
        $url = "https://api.sendgrid.com/v3/marketing/lists?page_size=100";
        $type = "GET";
        $data = "{}";
        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response, false);
    }

    /**
     * @param $curl
     * @param $api_key
     * @return mixed
     */
    public function getContacts()
    {
        $url = "https://api.sendgrid.com/v3/marketing/contacts";
        $type = "GET";
        $data = "{}";
        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response, false);
    }

    public function getUnsubscriberGroup()
    {
        $url = "https://api.sendgrid.com/v3/asm/groups";
        $type = "GET";
        $data = "{}";
        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response, false);
    }

    /**
     * @param $curl
     * @param $api_key
     * @param $list_subscriber_id
     * @throws \Exception
     */
    public function syncSubscriberToM2($list_subscriber_id)
    {
        $contacts = $this->getContacts();
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

    /**
     * @param $curl
     * @param $api_key
     * @param $list_subscriber_id
     * @param $unsubscriber_list_id
     */
    public function syncSubscriber($list_subscriber_id, $unsubscriber_list_id)
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
                $this->sync($subscriber, $list_subscriber_id);
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
                $this->syncUnsubscriber($unsubscriber_list_id, $arr2);
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
                $this->sync($subscriber, $list_subscriber_id);
            }
        }
    }

    /**
     * @param $list_unsubscriber_id
     * @param $list_email
     * @return mixed
     */
    public function syncUnsubscriber($list_unsubscriber_id, $list_email)
    {
        $url = "https://api.sendgrid.com/v3/asm/groups/$list_unsubscriber_id/suppressions";
        $type = "POST";
        $data = "{\"recipient_emails\":[$list_email]}";
        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response);
    }

    public function getDataSinglesend($id)
    {
        $url = "https://api.sendgrid.com/v3/marketing/singlesends/$id";
        $type = "GET";
        $data = "{}";
        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response, false);
    }

    public function deleteSingleSend($singlesend_id)
    {
        $url = "https://api.sendgrid.com/v3/marketing/singlesends/$singlesend_id";
        $type = "DELETE";
        $data = "{}";
        return $this->sendRestApi($url, $type, $data);
    }

    public function getAllSenders()
    {
        $url = "https://api.sendgrid.com/v3/marketing/senders";
        $type = "GET";
        $data = "{}";

        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response);
    }


    public function schedule($singlesendId, $date)
    {
        $url = "https://api.sendgrid.com/v3/marketing/singlesends/$singlesendId/schedule";
        $type = "PUT";
        $data = "{\"send_at\":\"$date\"}";
        $this->sendRestApi($url, $type, $data);
    }

    /**
     * @param $api_key
     * @return bool
     */
    public function testAPI($api_key)
    {
        $url = "https://api.sendgrid.com/v3/scopes";
        $type = "GET";
        $data = "{}";
        $response = $this->sendRestApi($url, $type, $data);
        if (isset(json_decode($response)->scopes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $sg
     * @param $data
     */
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

    /**
     * @param $sg
     * @param $singleSendId
     * @param $data
     */
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
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    public function sendRestApi($url, $type, $data)
    {
        $curl = curl_init();
        $api_key = $this->getSendGridConfig('general', 'api_key');
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key",
                "content-type: application/json"
            ),
        ));

        return curl_exec($curl);
    }
}
