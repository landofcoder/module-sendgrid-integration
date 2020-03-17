<?php


namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Exception;
use Lof\SendGrid\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

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
        \Lof\SendGrid\Model\VersionsFactory $versionsFactory,
        DateTimeFactory $dateFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->_helperdata = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->version = $versionsFactory;
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
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->_objectManager->create(\Lof\SendGrid\Model\SingleSend::class)->load($id);

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Singlesend no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            if($data['status'] == '') {
                $data['status'] = 'draft';
            }
            $model->setData($data);
            if ($data['template_generation'] == '') {
                $data['template_generation'] = 'legacy';
            }
            $name = $data['name'];
            $status = $data['status'];
            if ($id) {
                $version = $this->version->create();
                $version_id = $version->getCollection()->addFieldToFilter('version_id',$model->getTemplateVersion())->getData()['0']['id'];
                $version->load($version_id);
                $version->setHtmlContent($data['html_content']);
                $version->setTemplateName($data['template_name']);
                $version->setTemplateGeneration($data['template_generation']);
                $version->setVersionName($data['version_name']);
                $version->save();
                $singlesendId = $model->getSinglesendId();
                $template_id = $model->getTemplateId();
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
                    CURLOPT_POSTFIELDS => "{\"name\":\"$name\",\"status\":\"$status\",\"template_id\":\"$template_id\"}",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Bearer $api_key"
                    ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $this->_helperdata->editVersion($api_key, $data['version_name'], $data['html_content'], $template_id, $model->getTemplateVersion());
            } else {
                $template_id =  $this->_helperdata->createTemplate($api_key, $data['template_name'], $data['template_generation'])->id;
                $version_id = $this->_helperdata->createVersion($api_key, $data['version_name'], $template_id, $data['html_content'])->id;
                $version = $this->version->create();
                $version->setVersionId($version_id);
                $version->setVersionName($data['version_name']);
                $version->setTemplateId($template_id);
                $version->setTemplateName($data['template_name']);
                $version->setTemplateGeneration($data['template_generation']);
                $version->setHtmlContent($data['html_content']);
                $version->setActive('1');
                $version->setGeneratePlaneContent('1');
                $version->setSubject('Subject');
                $version->setEditor('design');
                $version->setUpdateAt($this->_dateFactory->create()->gmtDate());
                $version->save();
                $model->setCreateDate($this->_dateFactory->create()->gmtDate());
                $model->setUpdateDate($this->_dateFactory->create()->gmtDate());
                $model->setTemplateId($template_id);
                $model->setTemplateVersion($version_id);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{\"name\":\"$name\",\"status\":\"$status\",\"template_id\":\"$template_id\"}",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Bearer $api_key"
                    ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
            }
            $model->setSinglesendId(json_decode($response)->id);
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
}
