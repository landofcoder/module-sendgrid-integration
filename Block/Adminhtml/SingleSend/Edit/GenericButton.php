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

namespace Lof\SendGrid\Block\Adminhtml\SingleSend\Edit;

use Magento\Backend\Block\Widget\Context;
use Lof\SendGrid\Model\SingleSend;

/**
 * Class GenericButton
 *
 * @package Lof\SendGrid\Block\Adminhtml\SingleSend\Edit
 */
abstract class GenericButton
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var SingleSend
     */
    private $singleSend;

    /**
     * @param Context $context
     * @param SingleSend $singleSend
     */
    public function __construct(Context $context, SingleSend $singleSend)
    {
        $this->singleSend = $singleSend;
        $this->context = $context;
    }

    /**
     * Return model ID
     *
     * @return int|null
     */
    public function getModelId()
    {
        return $this->context->getRequest()->getParam('entity_id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * @return SingleSend
     */
    public function getSingleSend()
    {
        $id = $this->getModelId();
        return $this->singleSend->load($id);
    }

    /**
     * @return bool
     */
    public function checkStatusTrigger()
    {
        $model = $this->getSingleSend();
        if ($model->getStatus() == "triggered") {
            return true;
        } else {
            return false;
        }
    }
}
