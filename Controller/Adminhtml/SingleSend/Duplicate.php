<?php
namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Lof\SendGrid\Helper\Data;

/**
 * Class Duplicate
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Duplicate extends \Lof\SendGrid\Controller\Adminhtml\SingleSend
{
    protected $_helperdata;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Data $helper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_helperdata = $helper;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Lof\SendGrid\Model\SingleSend::class);
                $model->load($id);
                $new_model = $this->_objectManager->create(\Lof\SendGrid\Model\SingleSend::class);
                $new_model->setName($model->getName());
                $new_model->setCreateDate($model->getCreateDate());
                $new_model->setUpdateDate($model->getUpdateDate());
                $new_model->setTemplateId($model->getTemplateId());
                $new_model->setTemplateVersion($model->getTemplateVersion());
                $new_model->setStatus($model->getStatus());
                $name = $new_model->getName();
                $status = $new_model->getStatus();
                $api_key = $this->_helperdata->getSendGridConfig('general', 'api_key');
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
                if(isset(json_decode($response)->errors)) {
                    $this->messageManager->addErrorMessage(__("Somethings went wrong. Maybe wrong Api key"));
                    return $resultRedirect->setPath('*/*/');
                }
                $new_model->setSinglesendId(json_decode($response)->id);
                $new_model->save();
                $this->messageManager->addSuccessMessage(__('You duplicated the Singlesend.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Singlesend to duplicate.'));
        return $resultRedirect->setPath('*/*/');
    }
}
