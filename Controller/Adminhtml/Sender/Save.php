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

namespace Lof\SendGrid\Controller\Adminhtml\Sender;

use Exception;
use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\ResourceModel\Sender\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class Save
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;
    /**
     * @var Data
     */
    private $_helperdata;
    /**
     * @var CollectionFactory
     */
    private $collection;

    /**
     * @param Context $context
     * @param Data $helper
     * @param CollectionFactory $collectionFactory
     * @param DateTimeFactory $dateFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        Data $helper,
        CollectionFactory $collectionFactory,
        DateTimeFactory $dateFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->_helperdata = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->collection = $collectionFactory;
        $this->_dateFactory = $dateFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $model = $this->_objectManager->create(\Lof\SendGrid\Model\Sender::class);
                if (!(filter_var($data['from'], FILTER_VALIDATE_EMAIL))) {
                    throw new \Exception(__('Please enter the correct email.'));
                }
                if (!(filter_var($data['reply_to'], FILTER_VALIDATE_EMAIL))) {
                    throw new \Exception(__('Please enter the correct email.'));
                }
                if (!$this->getRequest()->isPost()) {
                    throw new \Exception(__('Something went wrong while saving the Sender.'));
                }
                if (!isset($data['nick_name'])) {
                    throw new \Exception(__('Nick name is empty.'));
                }
                if (!isset($data['from'])) {
                    throw new \Exception(__('From email is empty.'));
                }
                if (!isset($data['address'])) {
                    throw new \Exception(__('Address is empty.'));
                }
                if (!isset($data['from_name'])) {
                    throw new \Exception(__('From name is empty.'));
                }
                if (!isset($data['city'])) {
                    throw new \Exception(__('City is empty.'));
                }
                if (!isset($data['country'])) {
                    throw new \Exception(__('Country is empty.'));
                }
                $model->setData($data);
                $model->setVerified('0');
                $model->setUpdateAt($this->_dateFactory->create()->gmtDate());
                $model->setCreateAt($this->_dateFactory->create()->gmtDate());
                $nickname = $data['nick_name'];
                $from = $data['from'];
                $fromName = $data['from_name'];
                $replyTo = $data['reply_to'];
                $address = $data['address'];
                $city = $data['city'];
                $country = $data['country'];
                $url = "https://api.sendgrid.com/v3/marketing/senders";
                $data = "{\"nickname\":\"$nickname\",\"from\":{\"email\":\"$from\",\"name\":\"$fromName\"},\"reply_to\":{\"email\":\"$replyTo\"},\"address\":\"$address\",\"city\":\"$city\",\"country\":\"$country\"}";
                $type = "POST";
                $response = $this->_helperdata->sendRestApi($url, $type, $data);
                $collection = $this->collection->create()
                    ->addFieldToFilter('nick_name', $model->getNickName())->getData();
                if (count($collection)) {
                    $this->messageManager->addErrorMessage(
                        __('You already have a sender identity with the same nickname.')
                    );
                    return $resultRedirect->setPath('*/*/');
                }
                if (isset(json_decode($response)->id)) {
                    $model->setSenderId(json_decode($response)->id);
                    try {
                        $model->save();
                        $this->messageManager->addSuccessMessage(
                            __('You have created the Sender. Please check your email and verify it')
                        );
                        $this->dataPersistor->clear('lof_sendgrid_senders');
                        return $resultRedirect->setPath('*/*/');
                    } catch (LocalizedException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Sender.'));
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Something went wrong while saving the Sender.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Sender.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
