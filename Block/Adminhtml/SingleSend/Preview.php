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
namespace Lof\SendGrid\Block\Adminhtml\SingleSend;

use Magento\Framework\View\Element\Template;

class Preview extends \Magento\Framework\View\Element\Template
{
    protected $urlBuilder;
    /**
     * @var \Lof\SendGrid\Model\VersionsFactory
     */
    private $_version;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Lof\SendGrid\Model\VersionsFactory $versionsFactory
    ) {
        parent::__construct($context);
        $this->_version = $versionsFactory;
    }
    public function execute()
    {
    }
    public function getVersion(){
        $version = $this->_version->create();
        $id = $this->getRequest()->getParam('entity_id');
        $version->load($id);
        return $version;

    }
}
