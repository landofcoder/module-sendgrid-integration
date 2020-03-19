<?php
namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Exception;
use Lof\SendGrid\Helper\Data;
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
    protected $dataPersistor;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;
    /**
     * @var \Lof\SendGrid\Model\VersionsFactory
     */
    private $version;
    /**
     * @var Data
     */
    private $_helperdata;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\SendGrid\Helper\Data $helper,
        FilterProvider $filterProvider,
        \Lof\SendGrid\Model\VersionsFactory $versionsFactory,
        DateTimeFactory $dateFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->_helperdata = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->version = $versionsFactory;
        $this->_filterProvider = $filterProvider;
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
        if($this->_helperdata->testAPI($api_key) == false) {
            $this->messageManager->addErrorMessage(__("Somethings went wrong. Please check your Api key"));
            return $resultRedirect->setPath('*/*/');
        }
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->_objectManager->create(\Lof\SendGrid\Model\SingleSend::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Singlesend no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $senderId = $data['sender_id'];
            $suppression_group_id = $data['suppression_group_id'];
            $list_ids = $data['list_ids'];
            $html = $this->getCmsFilterContent($data['html_content']);
            $html = preg_replace("/\s+|\n+|\r/", ' ', $html);
            $list = '';
            foreach ($list_ids as $key => $list_id) {
                if($key == '0') {
                    $list .= "\"".$list_id."\"";
                }
                else {
                    $list .= ",\"".$list_id."\"";
                }
            }
            $name = $data['name'];
            $model->setName($name)->setListIds(json_encode($list_ids))->setSenderId($senderId)->setSuppressionGroupId($suppression_group_id);
            if ($id) {
                if($model->getStatus() == "triggered") {
                    $this->messageManager->addErrorMessage(__("Single send has been Triggered. Can't edit or schedule this single send"));
                    return $resultRedirect->setPath('*/*/');
                }
                else {
                    $template_id = $model->getTemplateId();
                    $version = $this->version->create();
                    $version_id = $version->getCollection()->addFieldToFilter('version_id',$model->getTemplateVersion())->getData()['0']['id'];
                    $version->load($version_id);
                    $version->setHtmlContent($data['html_content']);
                    $version->setTemplateName($data['template_name']);
                    $version->setTemplateGeneration($data['template_generation']);
                    $version->setVersionName($data['version_name']);
                    $version->setSubject($data['subject']);
                    $version->save();
                    $singlesendId = $model->getSinglesendId();
                    $model->setUpdateDate($this->_dateFactory->create()->gmtDate());
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends/$singlesendId",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "PATCH",
                        CURLOPT_POSTFIELDS => "{\"name\":\"$name\",\"sender_id\":$senderId,\"suppression_group_id\":$suppression_group_id,\"filter\":{\"list_ids\":[$list]}}",
                        CURLOPT_HTTPHEADER => array(
                            "authorization: Bearer $api_key"
                        ),
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    $this->_helperdata->editVersion($api_key, $data['version_name'], $html, $template_id, $model->getTemplateVersion());
                }
            } else {
                $template_id =  $this->_helperdata->createTemplate($api_key, $data['template_name'], $data['template_generation'])->id;
                $version_id = $this->_helperdata->createVersion($api_key, $data['version_name'], $template_id, $html)->id;
                $version = $this->version->create();
                $version->setVersionId($version_id);
                $version->setVersionName($data['version_name']);
                $version->setTemplateId($template_id);
                $version->setTemplateName($data['template_name']);
                $version->setTemplateGeneration('legacy');
                $version->setHtmlContent($data['html_content']);
                $version->setActive('1');
                $version->setGeneratePlaneContent('1');
                $version->setSubject('subject');
                $version->setEditor('design');
                $version->setUpdateAt($this->_dateFactory->create()->gmtDate());
                $version->save();
                $model->setCreateDate($this->_dateFactory->create()->gmtDate());
                $model->setUpdateDate($this->_dateFactory->create()->gmtDate());
                $model->setTemplateId($template_id);
                $model->setTemplateVersion($version_id);
                $model->setStatus('draft');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{\"name\":\"$name\",\"status\":\"draft\",\"template_id\":\"$template_id\",\"sender_id\":$senderId,\"suppression_group_id\":$suppression_group_id,\"filter\":{\"list_ids\":[$list]}}",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Bearer $api_key"
                    ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $model->setSinglesendId(json_decode($response)->id);
            }
            if($data['schedule'] == 1) {
                if($data['schedule_at'] == '1') {
                    $date = 'now';
                }
                else {
                    $date = $data['send_at'];
                    if($date < $this->_dateFactory->create()->gmtDate()) {
                        $this->messageManager->addErrorMessage(__("Can't schedule send single send in the past. Please enter a time in the future"));
                        return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
                    }
                }
                $this->_helperdata->schedule($api_key, $model->getSinglesendId(), $date);
                $model->setStatus('scheduled');
            }
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Singlesend.'));
                $this->dataPersistor->clear('lof_sendgrid_singlesend');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Singlesend.'));
            }
            $this->dataPersistor->set('lof_sendgrid_singlesend', $data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }
}
