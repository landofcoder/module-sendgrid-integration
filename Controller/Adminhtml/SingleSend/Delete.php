<?php


namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Lof\SendGrid\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

/**
 * Class Delete
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Delete extends \Lof\SendGrid\Controller\Adminhtml\SingleSend
{
    protected $_helperdata;
    public function __construct(
        Context $context,
        Data $helper,
        Registry $coreRegistry
    ) {
        $this->_helperdata = $helper;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Lof\SendGrid\Model\SingleSend::class);
                $model->load($id);
                $api_key = $this->_helperdata->getSendGridConfig('general', 'api_key');
                $singlesend_id = $model->getSinglesendId();
                $response = $this->_helperdata->deleteSingleSend($singlesend_id);
                if (isset(json_decode($response)->errors)) {
                    $this->messageManager->addErrorMessage(__("Somethings went wrong. Maybe wrong Api key"));
                    return $resultRedirect->setPath('*/*/');
                }
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Singlesend.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Singlesend to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
