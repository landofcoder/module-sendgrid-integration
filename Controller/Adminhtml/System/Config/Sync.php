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
     */
    public function execute()
    {
        $curl = curl_init();
        $token = $this->helper->getSendGridConfig('general', 'api_key'); //get Api Key
        //save single send
        $this->SyncSingleSend($curl, $token);

        // sync sender
        $this->SyncSender();

        //sync unscriber and unsubscriber groups
        $this->SyncContact($curl, $token);

        curl_close($curl);

        //sync customer to new database
        $this->moveCustomerToSubscriberGroup();

        $resultRedirect = $this->resultRedirectFactory->create();
        $this->_messageManager->addSuccessMessage(__("Sync with Sendgrid successfully."));
        return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
    }
}
