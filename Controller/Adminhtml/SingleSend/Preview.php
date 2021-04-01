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

use Lof\SendGrid\Model\SingleSendFactory;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Preview extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    /**
     * @var FilterProvider
     */
    private $_filterProvider;
    /**
     * @var SingleSendFactory
     */
    private $_singlesend;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FilterProvider $filterProvider
     * @param SingleSendFactory $singleSendFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FilterProvider $filterProvider,
        SingleSendFactory $singleSendFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->_singlesend = $singleSendFactory;
        $this->_filterProvider = $filterProvider;
    }

    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $singlesend = $this->_singlesend->create();
        $id = $this->getRequest()->getParam('entity_id');
        $singlesend->load($id);
        echo $this->getCmsFilterContent($singlesend->getHtmlContent());
        return;
    }

    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }
}
