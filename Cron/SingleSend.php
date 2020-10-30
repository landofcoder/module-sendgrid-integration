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
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Cron
 */
class SingleSend
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;
    /**
     * @var SingleSendFactory
     */
    private $singlesend;

    /**
     * SingleSend constructor.
     * @param Data $helper
     * @param SingleSendFactory $singleSendFactory
     * @param DateTimeFactory $dateFactory
     */
    public function __construct(
        Data $helper,
        SingleSendFactory $singleSendFactory,
        DateTimeFactory $dateFactory
    ) {
        $this->helper = $helper;
        $this->_dateFactory = $dateFactory;
        $this->singlesend = $singleSendFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $cron_enable = $this->helper->getSendGridConfig('sync', 'cron_enable');
        if ($cron_enable) {
            $curl = curl_init();
            $token = $this->helper->getSendGridConfig('general', 'api_key');

            $object = $this->helper->getAllSingleSend($token);
            if (isset($object->errors)) {
                return;
            }
            $items = get_object_vars($object)['result'];
            foreach ($items as $item) {
                if (!isset($item->id)) {
                    continue;
                }
                $model = $this->singlesend->create();
                $data = $this->helper->getDataSinglesend($curl, $item->id, $token);
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
                    $new_date_format = date('Y-m-d H:i:s', strtotime($item->updated_at));
                    $model->setUpdateDate($new_date_format);
                }
                if (isset($item->created_at)) {
                    $new_date_format = date('Y-m-d H:i:s', strtotime($item->created_at));
                    $model->setCreateDate($new_date_format);
                }
                if (isset($item->status)) {
                    $model->setStatus($item->status);
                }
                if (isset($data->send_at)) {
                    $new_date_format = date('Y-m-d H:i:s', strtotime($item->send_at));
                    $model->setSendAt($new_date_format);
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
            curl_close($curl);
        }
    }
}
