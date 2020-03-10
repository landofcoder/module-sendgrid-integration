<?php
namespace Lof\SendGrid\Block\Adminhtml\System\Config;

<<<<<<< HEAD
use Magento\Backend\Model\UrlInterface;
=======
>>>>>>> create module settings, menu, model, database
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ButtonSync extends Field
{
    /**
<<<<<<< HEAD
     * @param UrlInterface $backendUrl
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        UrlInterface $backendUrl,
        Context $context,
=======
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Block\Template\Context $context,
>>>>>>> create module settings, menu, model, database
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
<<<<<<< HEAD
        $url = $this->_backendUrl->getUrl("lof_sendgrid/system_config/sync", []);
        $html .= '
        <div class="pp-buttons-container"><button type="button" id="sync"><span><span><span>'.__("Sync").'</span></span></span></button></div>
        <style>
        #sync{
            width:31px;
            background-color: #e3e3e3;
            border-color: #adadad;
=======
        $url = $this->_backendUrl->getUrl("lof_barcode/system_config/printpaper", []);
        $html .= '
        <div class="pp-buttons-container"><button type="button" id="sync"><span><span><span>'.__("Sync").'</span></span></span></button></div>
        <style>
        #btn_id{
            width:558px;
            background-color: #eb5202;
            color: #fff;
>>>>>>> create module settings, menu, model, database
        }
        </style>';
        $html .= "
        <script>
        require([
                'jquery'
            ],
            function($) {
<<<<<<< HEAD
                $('#sync').on('click', function(){
                    window.location = '".$url."';
=======
                $('#print').on('click', function(){
                    
                    window.open('$url.');
>>>>>>> create module settings, menu, model, database
                });
        });</script>";
        return $html;
    }
}
