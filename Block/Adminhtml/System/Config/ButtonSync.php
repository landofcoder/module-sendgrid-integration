<?php
namespace Lof\SendGrid\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ButtonSync extends Field
{
    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }
    /**
     * Add color picker
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = "";
        $url = $this->_backendUrl->getUrl("lof_sendgrid/system_config/sync", []);
        $html .= '
        <div class="pp-buttons-container"><button type="button" id="sync"><span><span><span>'.__("Sync").'</span></span></span></button></div>
        <style>
        #sync{
            width:31px;
            background-color: #e3e3e3;
            border-color: #adadad;
        }
        </style>';
        $html .= "
        <script>
        require([
                'jquery'
            ],
            function($) {
                $('#sync').on('click', function(){
                    window.open('".$url."');
//                    window.location = '".$url."';
                });
        });</script>";
        return $html;
    }
}
