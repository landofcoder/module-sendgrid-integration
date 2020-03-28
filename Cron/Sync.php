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

use Lof\SendGrid\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class Sync
 *
 * @package Lof\SendGrid\Cron
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
            if(isset($item->name)) {
                if ($item->name == $unsubscriber_list) {
                    $unsubscriber_id = $item->id;
                }
                if ($item->name == $other_list) {
                    $other_list_id = $item->id;
                }
            }
        }
        $addressBookCollection = $this->addressBookCollection->create()->addFieldToFilter('is_subscribed', '0')->addFieldToFilter('is_synced','0');
        $list_other_email = '';
        foreach ($addressBookCollection as $addressBook) {
            if ($list_other_email == '') {
                $list_other_email .= "\"".$addressBook->getEmailAddress()."\"";
            } else {
                $list_other_email .= ",\"".$addressBook->getEmailAddress()."\"";
            }
        }
        if($list_other_email != '') {
            $response = $this->helper->syncUnsubscriber($curl, $api_key, $other_list_id, $list_other_email);
            if(count($response->recipient_emails) > 0) {
                foreach ($addressBookCollection as $addressBook) {
                    $addressBook->setIsSynced('1');
                    $addressBook->save();
                }
            }
        }
        $this->helper->syncSubscriber($curl, $api_key, $list_subscriber_id, $unsubscriber_id);
        $this->helper->syncSubscriberToM2($curl, $api_key, $list_subscriber_id);
        curl_close($curl);
    }
}
