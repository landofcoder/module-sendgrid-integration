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
