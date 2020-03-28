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
            if (isset($object->errors)) {
                $this->_messageManager->addErrorMessage(__("Some thing went wrong. May be wrong Api key"));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('adminhtml/system_config/edit/section/sendgrid/');
            }
            $items = get_object_vars($object)['result'];
            foreach ($items as $item) {
                $model = $this->singlesend->create();
                $existing = $model->getCollection()->addFieldToFilter("singlesend_id", $item->id)->getData();
                $data = $this->helper->getDataSinglesend($item->id, $token);
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
                    if (isset($data_version['0']->html_content)) {
                        $version->setHtmlContent($data_version['0']->html_content);
                    }
                    if (isset($data_version['0']->plain_content)) {
                        $version->setPlainContent($data_version['0']->plain_content);
                    }
                    if (isset($data_version['0']->generate_plain_content)) {
                        $version->setGeneratePlainContent($data_version['0']->generate_plain_content);
                    }
                    $version->setUpdateAt($data_version['0']->updated_at);
                    $version->setEditor($data_version['0']->editor);
                    if (isset($data_version['0']->subject)) {
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
                    if (isset($data_version['0']->html_content)) {
                        $version->setHtmlContent($data_version['0']->html_content);
                    }
                    if (isset($data_version['0']->plain_content)) {
                        $version->setPlainContent($data_version['0']->plain_content);
                    }
                    if (isset($data_version['0']->generate_plain_content)) {
                        $version->setGeneratePlainContent($data_version['0']->generate_plain_content);
                    }
                    $version->setUpdateAt($data_version['0']->updated_at);
                    $version->setEditor($data_version['0']->editor);
                    if (isset($data_version['0']->subject)) {
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
                    if (isset($data->send_at)) {
                        $model->setSendAt($data->send_at);
                    }
                    if (isset($data->sender_id)) {
                        $model->setSenderId($data->sender_id);
                    }
                    if (isset($data->suppression_group_id)) {
                        $model->setSuppressionGroupId($data->suppression_group_id);
                    }
                    if (isset($data->filter->list_ids)) {
                        $model->setListIds(json_encode($data->filter->list_ids));
                    }
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
                    if (isset($data->send_at)) {
                        $model->setSendAt($data->send_at);
                    }
                    if (isset($data->sender_id)) {
                        $model->setSenderId($data->sender_id);
                    }
                    if (isset($data->suppression_group_id)) {
                        $model->setSuppressionGroupId($data->suppression_group_id);
                    }
                    if (isset($data->filter->list_ids)) {
                        $model->setListIds(json_encode($data->filter->list_ids));
                    }
                    $model->save();
                }
            }
        }
    }
}
