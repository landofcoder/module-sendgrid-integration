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
namespace Lof\SendGrid\Block\Adminhtml\Statistics;

use Magento\Framework\View\Element\Template;
use Magento\Framework\UrlInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $urlBuilder;
    /**
     * @var \Lof\SendGrid\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        UrlInterface $urlBuilder,
        \Lof\SendGrid\Helper\Data $helper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_filterProvider = $filterProvider;
        $this->helper = $helper;
    }
    public function execute()
    {
    }
    public function getStatistics() {

        $curl = curl_init();
        $api_key = $this->helper->getSendGridConfig('general','api_key');
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/stats?start_date=2020-03-15&aggregated_by=day",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer $api_key"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return json_decode($response);
    }
}
