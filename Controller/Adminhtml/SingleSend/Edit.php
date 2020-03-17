<?php


namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

/**
 * Class Edit
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Edit extends \Lof\SendGrid\Controller\Adminhtml\SingleSend
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager->create(\Lof\SendGrid\Model\SingleSend::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Singlesend no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('lof_sendgrid_singlesend', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Singlesend') : __('New Singlesend'),
            $id ? __('Edit Singlesend') : __('New Singlesend')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Singlesends'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Singlesend %1', $model->getId()) : __('New Singlesend'));
        return $resultPage;
    }
}
