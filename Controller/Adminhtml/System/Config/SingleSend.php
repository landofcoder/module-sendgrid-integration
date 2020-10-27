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

namespace Lof\SendGrid\Controller\Adminhtml\System\Config;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\SingleSendFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Controller\System\Config
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
     * @return void
     */
    public function execute()
    {
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
        $items = get_object_vars($object)['result'];
        foreach ($items as $item) {
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
