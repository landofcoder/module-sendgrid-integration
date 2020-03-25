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
     * @var \Lof\SendGrid\Model\SingleSendFactory
     */
    private $singlesend;
    /**
     * @var \Lof\SendGrid\Model\VersionsFactory
     */
    private $_version;
    /**
     * @var \Lof\SendGrid\Model\SenderFactory
     */
    private $_sender;
    /**
     * @var \Lof\SendGrid\Model\SubscriberFactory
     */
    private $_subcriber;
    /**
     * @var \Lof\SendGrid\Model\UnSubscriberFactory
     */
    private $_unsubscriber;

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
        \Lof\SendGrid\Model\SingleSendFactory $singleSendFactory,
        \Lof\SendGrid\Model\VersionsFactory $versionsFactory,
        \Lof\SendGrid\Model\SenderFactory $senderFactory,
        \Lof\SendGrid\Model\SubscriberFactory $subscriber,
        \Lof\SendGrid\Model\UnSubscriberFactory $unsubscriber,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
    ) {
        $this->helper = $helper;
        $this->addressBookCollection = $addressBookCollection;
        $this->subscriberFactory= $subscriberFactory;
        $this->singlesend = $singleSendFactory;
        $this->_version = $versionsFactory;
        $this->_sender = $senderFactory;
        $this->_subscriber = $subscriber;
        $this->_unsubscriber = $unsubscriber;
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
        $token = $this->helper->getSendGridConfig('general', 'api_key');
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
        $object = json_decode($collection, false);
        if(isset($object->errors)) {
            $this->_messageManager->addErrorMessage(__("Some thing went wrong. May be wrong Api key"));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
        }
        $items = get_object_vars($object)['result'];
        foreach ($items as $item) {
            $model = $this->singlesend->create();
            $existing = $model->getCollection()->addFieldToFilter("singlesend_id", $item->id)->getData();
            $template_id = $this->helper->getTemplateId($item->id, $token);
            $data_template = $this->helper->getTemplate($template_id, $token);
            $data_version = $data_template->versions;
            $version = $this->_version->create();
            $existing_version = $version->getCollection()->addFieldToFilter("version_id", $data_version['0']->id)->getData();
            if (count($existing_version) == 0) {
                $version->setVersionId($data_version['0']->id);
                $version->setTemplateId($data_version['0']->template_id);
                $version->setActive($data_version['0']->active);
                $version->setTemplateName($data_template->name);
                $version->setTemplateGeneration($data_template->generation);
                $version->setVersionName($data_version['0']->name);
                $version->setHtmlContent($data_version['0']->html_content);
                $version->setPlainContent($data_version['0']->plain_content);
                $version->setGeneratePlainContent($data_version['0']->generate_plain_content);
                $version->setUpdateAt($data_version['0']->updated_at);
                $version->setEditor($data_version['0']->editor);
                if(isset($data_version['0']->subject)) {
                    $version->setSubject($data_version['0']->subject);
                }
                $version->save();
            } else {
                $id = $existing_version[0]['id'];
                $version->load($id);
                $version->setVersionId($data_version['0']->id);
                $version->setTemplateId($data_version['0']->template_id);
                $version->setActive($data_version['0']->active);
                $version->setTemplateName($data_template->name);
                $version->setTemplateGeneration($data_template->generation);
                $version->setVersionName($data_version['0']->name);
                $version->setHtmlContent($data_version['0']->html_content);
                $version->setPlainContent($data_version['0']->plain_content);
                $version->setGeneratePlainContent($data_version['0']->generate_plain_content);
                $version->setUpdateAt($data_version['0']->updated_at);
                $version->setEditor($data_version['0']->editor);
                if(isset($data_version['0']->subject)) {
                    $version->setSubject($data_version['0']->subject);
                }
                $version->save();
            }
            if (count($existing) == 0) {
                $model->setSinglesendId($item->id);
                $model->setName($item->name);
                $model->setUpdateDate($item->updated_at);
                $model->setCreateDate($item->created_at);
                $model->setStatus($item->status);
                $model->setTemplateId($template_id);
                $model->setTemplateVersion($data_version['0']->id);
                $model->save();
            } else {
                $entity_id = $existing[0]['entity_id'];
                $model->load($entity_id);
                $model->setSinglesendId($item->id);
                $model->setName($item->name);
                $model->setUpdateDate($item->updated_at);
                $model->setCreateDate($item->created_at);
                $model->setStatus($item->status);
                $model->setTemplateId($template_id);
                $model->setTemplateVersion($data_version['0']->id);
                $model->save($model);
            }
        }
        $senders = $this->helper->getAllSenders($api_key);
        foreach ($senders as $sender) {
            $model = $this->_sender->create();
            $exits = $model->getCollection()->addFieldToFilter('sender_id',$sender->id)->getData();
            if(count($exits) == 0) {
                $model->setNickName($sender->nickname)
                    ->setSenderId($sender->id)
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
            else {
                $model->load($exits['0']['id']);
                $model->setNickName($sender->nickname)
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
        }
        if ($this->helper->getSendGridConfig('general', 'add_customer') == 1) {
            $subscriber_list = $this->helper->getSendGridConfig('general', 'list_for_new_customer');
        } else {
            $subscriber_list = $this->helper->getSendGridConfig('general', 'subscribe_list');
        }
        $unsubscriber_list = $this->helper->getSendGridConfig('general', 'unsubscribe_list');
        $other_list = $this->helper->getSendGridConfig('general', 'other_group');
        $list_subscriber_id = '';
        $list = $this->helper->getAllList($curl, $api_key);
        $items = get_object_vars($list)['result'];
        foreach ($items as $item) {
            if (isset($item->name)) {
                if ($item->name == $subscriber_list) {
                    $list_subscriber_id = $item->id;
                }
            }
        }
        $list_unsubscriber = $this->helper->getUnsubscriberGroup($curl, $api_key);
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
        $addressBookCollection = $this->addressBookCollection->create()->addFieldToFilter('is_subscribed', '0')->addFieldToFilter('is_synced', '0');
        $list_other_email = '';
        foreach ($addressBookCollection as $addressBook) {
            if ($list_other_email == '') {
                $list_other_email .= "\"".$addressBook->getEmailAddress()."\"";
            } else {
                $list_other_email .= ",\"".$addressBook->getEmailAddress()."\"";
            }
        }
        if ($list_other_email != '') {
            $response = $this->helper->syncUnsubscriber($curl, $api_key, $other_list_id, $list_other_email);
            if (count($response->recipient_emails) > 0) {
                foreach ($addressBookCollection as $addressBook) {
                    $addressBook->setIsSynced('1');
                    $addressBook->save();
                }
            }
        }
        $this->helper->syncSubscriber($curl, $api_key, $list_subscriber_id, $unsubscriber_id);
        $this->helper->syncSubscriberToM2($curl, $api_key, $list_subscriber_id);
        $subscribers_groups = $this->helper->getAllList();
        $subscribers_groups = get_object_vars($subscribers_groups)['result'];
        foreach ($subscribers_groups as $subscribers_group) {
            $model = $this->_subscriber->create();
            $exits = $model->getCollection()->addFieldToFilter('subscriber_group_id',$subscribers_group->id)->getData();
            if(count($exits) == 0) {
                $model->setSubscriberGroupId($subscribers_group->id)
                    ->setSubscriberGroupName($subscribers_group->name)
                    ->setSubscriberCount($subscribers_group->contact_count);
                $model->save();
            }
            else {
                $model->load($exits['0']['id']);
                $model->setSubscriberGroupId($subscribers_group->id)
                    ->setSubscriberGroupName($subscribers_group->name)
                    ->setSubscriberCount($subscribers_group->contact_count);
                $model->save();
            }
        }
        $unsubscribers_groups = $this->helper->getUnsubscriberGroup();
        foreach ($unsubscribers_groups as $unsubscribers_group) {
            $model = $this->_unsubscriber->create();
            $exits = $model->getCollection()->addFieldToFilter('unsubscriber_group_id',$unsubscribers_group->id)->getData();
            if(count($exits) == 0) {
                $model->setUnsubscriberGroupId($unsubscribers_group->id)
                    ->setUnsubscriberGroupName($unsubscribers_group->name)
                    ->setUnsubscriberCount($unsubscribers_group->unsubscribes);
                $model->save();
            }
            else {
                $model->load($exits['0']['id']);
                $model->setUnsubscriberGroupId($unsubscribers_group->id)
                    ->setUnsubscriberGroupName($unsubscribers_group->name)
                    ->setUnsubscriberCount($unsubscribers_group->unsubscribes);
                $model->save();
            }
        }
        curl_close($curl);
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
    }
}
