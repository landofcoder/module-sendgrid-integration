<?php
namespace Lof\SendGrid\Block\Adminhtml\System\Config;

use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestApi extends Field
{
    /**
     * @param UrlInterface $backendUrl
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        UrlInterface $backendUrl,
        Context $context,
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
        $url = $this->_backendUrl->getUrl("lof_sendgrid/system_config/testapi", []);
        $html .= '
        <div class="pp-buttons-container"><button type="button" id="test"><span><span><span>'.__("Test Api Key").'</span></span></span></button></div>
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
                $('#test').on('click', function(){
                    window.location = '".$url."';
                });
        });</script>";
        return $html;
    }
}
