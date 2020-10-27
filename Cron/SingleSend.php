<?php
/**
 * LandOfCoder
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
 * @category   LandOfCoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2020 Landofcoder (http://www.LandOfCoder.com/)
 * @license    http://www.LandOfCoder.com/LICENSE-1.0.html
 */

namespace Lof\SendGrid\Cron;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\SingleSendFactory;
use Magento\Backend\App\Action\Context;
use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Cron
 */
class SingleSend extends \Magento\Backend\App\Action
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var SingleSendFactory
     */
    private SingleSendFactory $singlesend;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $helper
     * @param SingleSendFactory $singlesend
     */
    public function __construct(
        Context $context,
        Data $helper,
        SingleSendFactory $singlesend
    ) {
        $this->singlesend = $singlesend;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $cron_enable = $this->helper->getSendGridConfig('sync', 'cron_enable');
        if ($cron_enable == 1) {
            $token = $this->helper->getSendGridConfig('general', 'api_key');
            $httpHeaders = new Headers();
            $httpHeaders->addHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]);
            $request = new Request();
            $request->setHeaders($httpHeaders);
            $request->setUri('https://api.sendgrid.com/v3/marketing/singlesends');
            $request->setMethod(Request::METHOD_GET);

            $params = new Parameters();

            $request->setQuery($params);
            $client = new Client();
            $options = [
                'adapter'   => 'Zend\Http\Client\Adapter\Curl',
                'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
                'maxredirects' => 0,
                'timeout' => 30
            ];
            $client->setOptions($options);
            $response = $client->send($request);
            $collection = ($response->getBody());
            $object = json_decode($collection, false);
            if (isset($object->errors)) {
                $this->_messageManager->addErrorMessage(__("Some thing went wrong. May be wrong Api key"));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
            }
            $items = get_object_vars($object)['result'];
            if ($items) {
                foreach ($items as $item) {
                    if ($item && is_object($item) && isset($item->id)) {
                        //save singlge send
                        $model = $this->singlesend->create();
                        $data = $this->helper->getDataSinglesend($item->id, $token);
                        $existing = $model->getCollection()->addFieldToFilter("singlesend_id", $item->id)->getData();
                        if (count($existing)) {
                            $entity_id = $existing[0]['entity_id'];
                            $model->load($entity_id);
                        }
                        if (isset($item->id)) {
                            $model->setSinglesendId($item->id);
                        }
                        if (isset($item->name)) {
                            $model->setName($item->name);
                        }
                        if (isset($item->updated_at)) {
                            $model->setUpdateDate($item->updated_at);
                        }
                        if (isset($item->created_at)) {
                            $model->setCreateDate($item->created_at);
                        }
                        if (isset($item->status)) {
                            $model->setStatus($item->status);
                        }
                        if (isset($data->send_at)) {
                            $model->setSendAt($data->send_at);
                        }
                        if (isset($data->email_config->sender_id)) {
                            $model->setSenderId($data->email_config->sender_id);
                        }
                        if (isset($data->email_config->suppression_group_id)) {
                            $model->setSuppressionGroupId($data->email_config->suppression_group_id);
                        }
                        if (isset($data->send_to->list_ids)) {
                            $model->setListIds(json_encode($data->send_to->list_ids));
                        }
                        if (isset($data->email_config->subject)) {
                            $model->setSubject($data->email_config->subject);
                        }
                        if (isset($data->email_config->html_content)) {
                            $model->setHtmlContent($data->email_config->html_content);
                        }
                        if (isset($data->email_config->plain_content)) {
                            $model->setPlainContent($data->email_config->plain_content);
                        }
                        if (isset($data->email_config->editor)) {
                            $model->setEditor($data->email_config->editor);
                        }
                        $model->save();
                    }
                }
            }
        }
    }
}
