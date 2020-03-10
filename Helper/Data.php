<?php
namespace Lof\SendGrid\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const XML_PATH_TAG = 'sendgrid/';
    const XML_PATH_SENDGRID = 'sendgrid/';
    const XML_PATH_SMTP = 'smtp/';


    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_customerFactory = $customerFactory;
        $this->scopeConfig=$scopeConfig;
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    public function getSendGridConfig($group, $code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SENDGRID .$group.'/'. $code, $storeId);
    }
    public function getSMTPConfig($group, $code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SMTP .$group.'/'. $code, $storeId);
    }
    public function getCustomerCollection()
    {
        return $this->_customerFactory->create();
    }
    public function getSingleSend()
    {
        $curl = curl_init();
        $api_key = $this->getSendGridConfig('general', 'api_key');
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/singlesends",
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

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }
}
