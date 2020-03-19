<?php
/**
 * Copyright (c) 2019  Landofcoder
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

namespace Lof\SendGrid\Cron;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Cron
 */
class SingleSend extends \Magento\Backend\App\Action
{
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Lof\SendGrid\Helper\Data $helper
     * @param \Lof\SendGrid\Model\SingleSendFactory $singlesend
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\SendGrid\Helper\Data $helper,
        \Lof\SendGrid\Model\VersionsFactory $versionsFactory,
        \Lof\SendGrid\Model\SingleSendFactory $singlesend
    ) {
        $this->_version = $versionsFactory;
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
            $httpHeaders = new \Zend\Http\Headers();
            $httpHeaders->addHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]);
            $request = new \Zend\Http\Request();
            $request->setHeaders($httpHeaders);
            $request->setUri('https://api.sendgrid.com/v3/marketing/singlesends');
            $request->setMethod(\Zend\Http\Request::METHOD_GET);

            $params = new \Zend\Stdlib\Parameters();

            $request->setQuery($params);
            $client = new \Zend\Http\Client();
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
                $model = $this->singlesend->create();
                $existing = $model->getCollection()->addFieldToFilter("singlesend_id", $item->id)->getData();
                $template_id = $this->helper->getTemplateId($item->id, $token);
                $data_template = $this->helper->getTemplate($template_id, $token);
                $data_version = $data_template->versions;
                $version = $this->_version->create();
                $existing_version = $version->getCollection()->addFieldToFilter("version_id", $data_version['0']->id)->getData();
                if (count($existing_version) == 0) {
                    $version->setVersionId($data_version['0']->id);
                    $version->setTemplateId($data_version['0']->template_id);
                    $version->setActive($data_version['0']->active);
                    $version->setTemplateName($data_template->name);
                    $version->setTemplateGeneration($data_template->generation);
                    $version->setVersionName($data_version['0']->name);
                    $version->setHtmlContent($data_version['0']->html_content);
                    $version->setPlainContent($data_version['0']->plain_content);
                    $version->setGeneratePlainContent($data_version['0']->generate_plain_content);
                    $version->setUpdateAt($data_version['0']->updated_at);
                    $version->setEditor($data_version['0']->editor);
                    if(isset($data_version['0']->subject)) {
                        $version->setSubject($data_version['0']->subject);
                    }
                    $version->save();
                } else {
                    $id = $existing_version[0]['id'];
                    $version->load($id);
                    $version->setVersionId($data_version['0']->id);
                    $version->setTemplateId($data_version['0']->template_id);
                    $version->setActive($data_version['0']->active);
                    $version->setTemplateName($data_template->name);
                    $version->setTemplateGeneration($data_template->generation);
                    $version->setVersionName($data_version['0']->name);
                    $version->setHtmlContent($data_version['0']->html_content);
                    $version->setPlainContent($data_version['0']->plain_content);
                    $version->setGeneratePlainContent($data_version['0']->generate_plain_content);
                    $version->setUpdateAt($data_version['0']->updated_at);
                    $version->setEditor($data_version['0']->editor);
                    if(isset($data_version['0']->subject)) {
                        $version->setSubject($data_version['0']->subject);
                    }
                    $version->save();
                }
                if (count($existing) == 0) {
                    $model->setSinglesendId($item->id);
                    $model->setName($item->name);
                    $model->setUpdateDate($item->updated_at);
                    $model->setCreateDate($item->created_at);
                    $model->setStatus($item->status);
                    $model->setTemplateId($template_id);
                    $model->setTemplateVersion($data_version['0']->id);
                    $model->save();
                } else {
                    $entity_id = $existing[0]['entity_id'];
                    $model->load($entity_id);
                    $model->setSinglesendId($item->id);
                    $model->setName($item->name);
                    $model->setUpdateDate($item->updated_at);
                    $model->setCreateDate($item->created_at);
                    $model->setStatus($item->status);
                    $model->setTemplateId($template_id);
                    $model->setTemplateVersion($data_version['0']->id);
                    $model->save($model);
                }
            }
        }
    }
}
