<?php


namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Lof\SendGrid\Controller\Adminhtml\SingleSend;
use Lof\SendGrid\Model\SingleSendFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Edit extends SingleSend
{

    protected $resultPageFactory;
    /**
     * @var SingleSendFactory
     */
    private $singleSend;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param SingleSendFactory $singleSend
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        SingleSendFactory $singleSend,
        PageFactory $resultPageFactory
    ) {
        $this->singleSend = $singleSend;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->singleSend->create();

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Single Send no longer exists.'));
                /** @var Redirect $resultRedirect */
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
