<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
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
     * @throws \Exception
     */
    public function execute()
    {
        $cron_enable = $this->helper->getSendGridConfig('sync', 'cron_enable');
        if ($cron_enable) {
            $singleSends = $this->helper->getAllSingleSend();
            if (!isset($singleSends->result) || !$singleSends->result) {
                return;
            }
            $items = $singleSends->result;
            $singleSendIds = [];
            foreach ($items as $item) {
                if (!isset($item->id)) {
                    continue;
                }
                $singleSendIds[] = $item->id;
                $model = $this->singlesend->create();
                $data = $this->helper->getDataSinglesend($item->id);
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
            $singleSendCollectionDelete = $this->singlesend->create()->getCollection();
            if ($singleSendIds) {
                $singleSendCollectionDelete->addFieldToFilter('singlesend_id', ['nin' => $singleSendIds]);
            }
            $singleSendCollectionDelete->walk('delete');
        }
    }
}
