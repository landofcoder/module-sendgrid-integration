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
namespace Lof\SendGrid\Controller\Adminhtml\Statistics;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    /**
     * @var \Lof\SendGrid\Helper\Data
     */
    private $_helperdata;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Lof\SendGrid\Helper\Data $helper
    ) {
        $this->_helperdata = $helper;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->_helperdata->testAPI() == false) {
            $this->messageManager->addErrorMessage(__("Somethings went wrong. Please check your Api key"));
            return;
        }
        return $this->_pageFactory->create();
    }
}
