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

namespace Lof\SendGrid\Model;

use Lof\SendGrid\Api\Data\SingleSendInterface;
use Lof\SendGrid\Api\Data\SingleSendInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Model
 */
class SingleSend extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'lof_sendgrid_singlesend';
    protected $dataObjectHelper;

    protected $singlesendDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param SingleSendInterfaceFactory $singlesendDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Lof\SendGrid\Model\ResourceModel\SingleSend $resource
     * @param \Lof\SendGrid\Model\ResourceModel\SingleSend\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        SingleSendInterfaceFactory $singlesendDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Lof\SendGrid\Model\ResourceModel\SingleSend $resource,
        \Lof\SendGrid\Model\ResourceModel\SingleSend\Collection $resourceCollection,
        array $data = []
    ) {
        $this->singlesendDataFactory = $singlesendDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve singlesend model with singlesend data
     * @return SingleSendInterface
     */
    public function getDataModel()
    {
        $singlesendData = $this->getData();

        $singlesendDataObject = $this->singlesendDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $singlesendDataObject,
            $singlesendData,
            SingleSendInterface::class
        );

        return $singlesendDataObject;
    }
}
