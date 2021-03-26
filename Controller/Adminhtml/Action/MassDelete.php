<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://www.landofcoder.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2020 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\SendGrid\Controller\Adminhtml\Action;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\ResourceModel\SingleSend\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action implements HttpPostActionInterface
{


    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Data
     */
    private $_helperData;


    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, Data $helper)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_helperData = $helper;
        parent::__construct($context);
    }


    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $singlesend) {
            $singlesend->delete();
            $api_key = $this->_helperData->getSendGridConfig('general', 'api_key');
            $singlesend_id = $singlesend->getSinglesendId();
            $response = $this->_helperData->deleteSingleSend($singlesend_id);
            if (isset(json_decode($response)->errors)) {
                $this->messageManager->addErrorMessage(__("Somethings went wrong. Maybe wrong Api key"));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));



        return $resultRedirect->setPath('*/singlesend/index');
    }
}
