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

namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Lof\SendGrid\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;

/**
 * Class Duplicate
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Duplicate extends \Lof\SendGrid\Controller\Adminhtml\SingleSend
{
    /**
     * @var Data
     */
    protected $_helperdata;
    /**
     * @var FilterProvider
     */
    private $_filterProvider;

    /**
     * Duplicate constructor.
     * @param Context $context
     * @param Data $helper
     * @param FilterProvider $filterProvider
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Data $helper,
        FilterProvider $filterProvider,
        Registry $coreRegistry
    ) {
        $this->_helperdata = $helper;
        $this->_filterProvider = $filterProvider;
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
                $new_model->setSuppressionGroupId($model->getSuppressionGroupId());
                $new_model->setListId($model->getListId());
                $new_model->setStatus('draft');
                $new_model->setHtmlContent($model->getHtmlContent());
                $new_model->setPlainContent($model->getPlainContent());
                $new_model->setSubject($model->getSubject());
                $new_model->setEditor($model->getEditor());
                $new_model->setSenderId($model->getSenderId());

                $name = $new_model->getName();
                $listSeller = json_encode($new_model->getListId());
                $subject = $new_model->getSubject();
                $plainContent = $new_model->getPlainContent();
                $editor = $new_model->getEditor();
                $suppression_group_id = $new_model->getSuppressionGroupId();
                $html = $this->getCmsFilterContent($new_model->getHtmlContent());
                $senderId = $new_model->getSenderId();
                $dataUpdate = '{"name":"' . $name . '","send_to":{"list_ids":' . $listSeller . '},"email_config":{"subject":"' . $subject . '","html_content":"' . $html . '","plain_content":"' . $plainContent . '","generate_plain_content":true,"editor":"' . $editor . '","suppression_group_id":' . $suppression_group_id . ',"sender_id":' . $senderId . '}}';
                $url = "https://api.sendgrid.com/v3/marketing/singlesends";
                $type = "POST";
                $response = $this->_helperdata->sendRestApi($url, $type, $dataUpdate);
                if (isset(json_decode($response)->errors)) {
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
