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
namespace Lof\SendGrid\Helper;

use Exception;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\ScopeInterface;
use Zend\Http\Client\Adapter\Curl;

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
     * @return mixed
     */
    public function getAllSingleSend()
    {
        $url = 'https://api.sendgrid.com/v3/marketing/singlesends';
        return $this->sendApi($url, "GET", []);
    }

    /**
     * @return Collection
     */
    public function getCustomerCollection()
    {
        return $this->_customerFactory->create();
    }

    /**
     * @param $contact
     * @param $list_id
     * @return bool
     */
    public function sync($contact, $list_id)
    {
        $url = "https://api.sendgrid.com/v3/marketing/contacts";
        $type = "PUT";
        $data = "{\"list_ids\":[\"$list_id\"],\"contacts\":[".$contact."]}";
        $response = $this->sendRestApi($url, $type, $data);
        if (!$list_id) {
            $this->_messageManager->addNoticeMessage(
                __("Please select Subscribe, UnSubscribe Group and save, then sync again.")
            );
            return false;
        } else {
            if (strpos($response, 'job_id')) {
                return true;
            } else {
                $this->_messageManager->addErrorMessage(__("Something went wrong. Can't sync contacts"));
                return false;
            }
        }
    }


    /**
     * @return mixed
     */
    public function getAllList()
    {
        $url = "https://api.sendgrid.com/v3/marketing/lists?page_size=100";
        $type = "GET";
        $data = "{}";
        $response = $this->sendRestApi($url, $type, $data);
        $res = json_decode($response, false);
        if (isset($res->result) && count($res->result)) {
            return $res;
        } else {
            $sub = $this->createNewSubscribeGroup("Magento 2 Subscriber Group");
            $arr['result'][0] = $sub;
            return $arr;
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function createNewSubscribeGroup($name)
    {
        $url = "https://api.sendgrid.com/v3/marketing/lists";
        $type = "POST";
        $data = "{\"name\":\"$name\"}";
        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response, false);
    }

    /**
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

    /**
     * @return mixed
     */
    public function getUnsubscriberGroup()
    {
        $url = "https://api.sendgrid.com/v3/asm/groups";
        $type = "GET";
        $data = "{}";
        $response = $this->sendRestApi($url, $type, $data);
        $res = json_decode($response, false);
        if (count($res)) {
            return $res;
        } else {
            $sub = $this->createNewUnsubscribeGroup("Magento 2 UnSubscriber Group", "Magento 2 UnSubscriber Group");
            $arr[0] = $sub;
            return $arr;
        }
    }

    /**
     * @param $name
     * @param $des
     * @return mixed
     */
    public function createNewUnsubscribeGroup($name, $des)
    {
        $url = "https://api.sendgrid.com/v3/asm/groups";
        $type = "POST";
        $data = "{\"name\":\"$name\",\"description\":\"$des\"}";
        $response = $this->sendRestApi($url, $type, $data);
        return json_decode($response, false);
    }

    /**
     * @param $list_subscriber_id
     * @throws Exception
     */
    public function syncSubscriberToM2($list_subscriber_id)
    {
        $contacts = $this->getContacts();
        if (isset($contacts->result) && $contacts->result) {
            $items = $contacts->result;
            foreach ($items as $item) {
                if (isset($item->list_ids)) {
                    if (in_array($list_subscriber_id, $item->list_ids)) {
                        $subscriber = $this->_subscriberFactory->create();
                        $existing = $subscriber->getCollection()
                            ->addFieldToFilter("subscriber_email", $item->email)->getFirstItem();
                        if ($existing->getData()) {
                            $subscriber->setSubscriberEmail($item->email)
                                ->setCustomerFirstname($item->first_name)
                                ->setCustomerLastname($item->last_name)
                                ->setStatus('1');
                            $subscriber->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $list_subscriber_id
     * @param $unsubscriber_list_id
     */
    public function syncSubscriber($list_subscriber_id, $unsubscriber_list_id)
    {
        $sub = $this->_subscriberFactory->create()->getCollection();
        $unsub = $this->_subscriberFactory->create()->getCollection();
        $subscriber = '';
        if ($this->getSendGridConfig('general', 'add_customer') == 0) {
            $sub->addFieldToFilter('subscriber_status', '1');
            $unsub->addFieldToFilter('subscriber_status', '3');
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

    /**
     * @param $id
     * @return mixed
     */
    public function getDataSinglesend($id)
    {
        $url = "https://api.sendgrid.com/v3/marketing/singlesends/$id";
        return $this->sendApi($url, "GET", []);
    }

    /**
     * @param $singlesend_id
     * @return bool|string
     */
    public function deleteSingleSend($singlesend_id)
    {
        $url = "https://api.sendgrid.com/v3/marketing/singlesends/$singlesend_id";
        $type = "DELETE";
        $data = "{}";
        return $this->sendRestApi($url, $type, $data);
    }

    /**
     * @return mixed
     */
    public function getAllSenders()
    {
        $url = "https://api.sendgrid.com/v3/marketing/senders";
        $type = "GET";

        return $this->sendApi($url, $type, []);
    }

    /**
     * @param $singlesendId
     * @param $date
     */
    public function schedule($singlesendId, $date)
    {
        $url = "https://api.sendgrid.com/v3/marketing/singlesends/$singlesendId/schedule";
        $type = "PUT";
        $data = "{\"send_at\":\"$date\"}";
        $this->sendRestApi($url, $type, $data);
    }

    /**
     * @return bool
     */
    public function testAPI()
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
     * @param $url
     * @param $type
     * @param $data
     * @return bool|string
     */
    public function sendRestApi($url, $type, $data)
    {
        $curl = curl_init();
        $api_key = $this->getSendGridConfig('general', 'api_key');
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer $api_key",
                "content-type: application/json"
            ],
        ]);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    /**
     * @param $url
     * @param $type
     * @param $data
     * @return mixed
     */
    public function sendApi($url, $type, $data)
    {
        $token = $this->getSendGridConfig('general', 'api_key');
        $httpHeaders = new \Zend\Http\Headers();
        $httpHeaders->addHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
        $request = new \Zend\Http\Request();
        $request->setHeaders($httpHeaders);
        $request->setUri($url);
        $request->setMethod($type);
        $params = new \Zend\Stdlib\Parameters($data);
        $request->setQuery($params);

        $client = new \Zend\Http\Client();
        $options = [
            'adapter'   => Curl::class,
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];
        $client->setOptions($options);
        $response = $client->send($request);
        $collection = ($response->getBody());
        return json_decode($collection, false);
    }
}
