<?php
/**
 * Copyright (c) 2020  Landofcoder
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lof\SendGrid\Controller\Adminhtml\SingleSend;

use Lof\SendGrid\Model\SingleSendFactory;
use Lof\SendGrid\Model\VersionsFactory;
use Magento\Cms\Model\Template\FilterProvider;

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
     * @var VersionsFactory
     */
    private $_version;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param VersionsFactory $versionsFactory
     * @param FilterProvider $filterProvider
     * @param SingleSendFactory $singleSendFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        VersionsFactory $versionsFactory,
        FilterProvider $filterProvider,
        SingleSendFactory $singleSendFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->_singlesend = $singleSendFactory;
        $this->_filterProvider = $filterProvider;
        $this->_version = $versionsFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $version = $this->getVersion();
        echo $this->getCmsFilterContent($version->getHtmlContent());
        return;
    }
    public function getVersion()
    {
        $version = $this->_version->create();
        $singlesend = $this->_singlesend->create();
        $id = $this->getRequest()->getParam('entity_id');
        $singlesend->load($id);
        $versions = $version->getCollection()->addFieldToFilter('version_id', $singlesend->getTemplateVersion())->getData();
        $version->load($versions['0']['id']);
        return $version;
    }
    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }
}
