<?php

namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Exception;
use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\SingleSendFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Cms\Model\Template\FilterProvider;

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
     * @var FilterProvider
     */
    private FilterProvider $_filterProvider;

    /**
     * @param Context $context
     * @param Data $helper
     * @param FilterProvider $filterProvider
     * @param DateTimeFactory $dateFactory
     * @param SingleSendFactory $singleSend
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        Data $helper,
        FilterProvider $filterProvider,
        DateTimeFactory $dateFactory,
        SingleSendFactory $singleSend,
        DataPersistorInterface $dataPersistor
    ) {
        $this->_helperdata = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->_filterProvider = $filterProvider;
        $this->singleSend = $singleSend;
        $this->_dateFactory = $dateFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $api_key = $this->_helperdata->getSendGridConfig('general', 'api_key');
        if ($this->_helperdata->testAPI($api_key) == false) {
            $this->messageManager->addErrorMessage(__("Somethings went wrong. Please check your Api key"));
            return $resultRedirect->setPath('*/*/');
        }
        $resultRedirect->setPath('*/*/');
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->singleSend->create();
            try {
                if (!isset($data['sender_id'])) {
                    throw new \Exception(__('Please select sender.'));
                }
                if (!isset($data['name'])) {
                    throw new \Exception(__('Please enter single send name.'));
                }
                if (!isset($data['suppression_group_id'])) {
                    throw new \Exception(__('Please select suppression group.'));
                }
                if (!isset($data['list_ids'])) {
                    throw new \Exception(__('Please select list customer to create single send.'));
                }
                if (!isset($data['html_content'])) {
                    throw new \Exception(__('Please enter email content.'));
                }
                if (!isset($data['subject'])) {
                    throw new \Exception(__('Please enter subject.'));
                }
                $senderId = $data['sender_id'];
                $html = $this->getCmsFilterContent($data['html_content']);
                $html = preg_replace("/\s+|\n+|\r/", ' ', $html);
                $suppression_group_id = $data['suppression_group_id'];
                $name = $data['name'];
                $data['list_ids'] = json_encode($data['list_ids']);
                $list = $data['list_ids'];

                $subject = $data['subject'];
                if ($id) {
                    $model->load($id);
                    $model->setData($data);
                    if ($model->getStatus() == "triggered") {
                        $this->messageManager->addErrorMessage(__("Single send has been Triggered. Can't edit or schedule this single send"));
                        return $resultRedirect;
                    } else {
                        $model->setUpdateDate($this->_dateFactory->create()->gmtDate());
                        $plainContent = $model->getPlainContent();
                        $editor = $model->getEditor();
                        $suppression_group_id = $model->getSuppressionGroupId();
                        $dataUpdate =  '{"name":"'.$name.'","send_to":{"list_ids":'.$list.'},"email_config":{"subject":"'.$subject.'","html_content":"'.$html.'","plain_content":"'.$plainContent.'","generate_plain_content":true,"editor":"'.$editor.'","suppression_group_id":'.$suppression_group_id.',"sender_id":'.$senderId.'}}';
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => $dataUpdate,
                            CURLOPT_HTTPHEADER => array(
                                "authorization: Bearer $api_key"
                            ),
                        ));
                        $response = curl_exec($curl);
                        $err = curl_error($curl);
                        if ($err) {
                            throw new \Exception(__($err));
                        }
                        curl_close($curl);
                    }
                } else {
                    $model->setData($data);
                    $model->setCreateDate($this->_dateFactory->create()->gmtDate());
                    $model->setUpdateDate($this->_dateFactory->create()->gmtDate());
                    $model->setStatus('draft');
                    $plainContent = $model->getPlainContent();
                    $editor = $model->getEditor();
                    $suppression_group_id = $model->getSuppressionGroupId();
                    $dataUpdate =  '{"name":"'.$name.'","send_to":{"list_ids":'.$list.'},"email_config":{"subject":"'.$subject.'","editor":"'.$editor.'","suppression_group_id":"'.$suppression_group_id.'","sender_id":"'.$senderId.'"}}';

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $dataUpdate,
                        CURLOPT_HTTPHEADER => array(
                            "authorization: Bearer $api_key"
                        ),
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    if ($err) {
                        throw new \Exception(__($err));
                    }
                    $model->setSinglesendId(json_decode($response)->id);
                }

                try {
                    $model->save();
                    if ($data['schedule']) {
                        if ($data['schedule_at']) {
                            $date = 'now';
                        } else {
                            $date = $data['send_at'];
                            if ($date < $this->_dateFactory->create()->gmtDate()) {
                                $this->messageManager->addErrorMessage(__("Can't schedule send single send in the past. Please enter a time in the future"));
                                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
                            }
                        }
                        $this->_helperdata->schedule($api_key, $model->getSinglesendId(), $date);
                        $model->setStatus('scheduled');
                        $model->save();
                    }
                    $this->messageManager->addSuccessMessage(__('You saved the Singlesend.'));
                    $this->dataPersistor->clear('lof_sendgrid_singlesend');

                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                    }
                    return $resultRedirect->setPath('*/*/');
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Single Send.'));
                }
                $this->dataPersistor->set('lof_sendgrid_singlesend', $data);
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect;
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }
}
