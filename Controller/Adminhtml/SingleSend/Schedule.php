<?php
namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Exception;
use Lof\SendGrid\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class Schedule
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Schedule extends \Magento\Backend\App\Action
{
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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\SendGrid\Helper\Data $helper,
        FilterProvider $filterProvider,
        DateTimeFactory $dateFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->_helperdata = $helper;
        $this->dataPersistor = $dataPersistor;
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
        var_dump($data);
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->_objectManager->create(\Lof\SendGrid\Model\SingleSend::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Singlesend no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            if ($id) {
                $singlesendId = $model->getSinglesendId();
                if($model->getStatus() == "scheduled") {
                    $this->messageManager->addErrorMessage(__("Single send has been scheduled. You can't schedule or edit this single send until finish this process"));
                    return $resultRedirect->setPath('*/*/');
                }
                if($data['schedule'] == 1){
                    $date = 'now';
                }
                else {
                    $date = $data['send_at'];
                }
                if($date < $this->_dateFactory->create()->gmtDate()) {
                    $this->messageManager->addErrorMessage(__("Can't schedule send single send in the past. Please enter a time in the future"));
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
                }
                $this->_helperdata->schedule($api_key, $singlesendId, $date);
            } else {
                $this->messageManager->addErrorMessage(__("Single send was not saved. Please save the single send then schedule it"));
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
            }
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You have scheduled the Singlesend.'));
                $this->dataPersistor->clear('lof_sendgrid_singlesend');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while schedule the Singlesend.'));
            }
            $this->dataPersistor->set('lof_sendgrid_singlesend', $data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
