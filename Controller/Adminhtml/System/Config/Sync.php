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

namespace Lof\SendGrid\Controller\Adminhtml\System\Config;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\SenderFactory;
use Lof\SendGrid\Model\SingleSendFactory;
use Lof\SendGrid\Model\UnSubscriberFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Lof\SendGrid\Model\AddressBookFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class Sync
 *
 * @package Lof\SendGrid\Controller\Adminhtml\System/Config
 */
class Sync extends \Lof\SendGrid\Controller\Adminhtml\Sync
{

    /**
     * Sync constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param Data $helper
     * @param CollectionFactory $orderCollectionFactory
     * @param ManagerInterface $messageManager
     * @param \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection
     * @param SingleSendFactory $singleSendFactory
     * @param SenderFactory $senderFactory
     * @param DateTimeFactory $dateFactory
     * @param AddressBookFactory $addressBookFactory
     * @param \Lof\SendGrid\Model\SubscriberFactory $subscriber
     * @param UnSubscriberFactory $unsubscriber
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Data $helper,
        CollectionFactory $orderCollectionFactory,
        ManagerInterface $messageManager,
        \Lof\SendGrid\Model\ResourceModel\AddressBook\CollectionFactory $addressBookCollection,
        SingleSendFactory $singleSendFactory,
        SenderFactory $senderFactory,
        DateTimeFactory $dateFactory,
        AddressBookFactory $addressBookFactory,
        \Lof\SendGrid\Model\SubscriberFactory $subscriber,
        UnSubscriberFactory $unsubscriber,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subcriberCollectionFactory
    ) {
        parent::__construct(
            $context,
            $helper,
            $orderCollectionFactory,
            $messageManager,
            $addressBookCollection,
            $singleSendFactory,
            $senderFactory,
            $dateFactory,
            $addressBookFactory,
            $subscriber,
            $unsubscriber,
            $subcriberCollectionFactory
        );
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        //save single send
        $this->SyncSingleSend();

        // sync sender
        $this->SyncSender();

        //sync customer to new database
        $this->moveCustomerToSubscriberGroup();

        //sync unscriber and unsubscriber groups
        $this->SyncContact();

        $resultRedirect = $this->resultRedirectFactory->create();
        $this->_messageManager->addSuccessMessage(__("Sync with Sendgrid successfully."));
        return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
    }
}
