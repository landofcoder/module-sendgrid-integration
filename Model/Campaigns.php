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

use Lof\SendGrid\Api\Data\CampaignsInterface;
use Lof\SendGrid\Api\Data\CampaignsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Campaigns
 *
 * @package Lof\SendGrid\Model
 */
class Campaigns extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'lof_sendgrid_campaigns';
    protected $dataObjectHelper;

    protected $campaignsDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CampaignsInterfaceFactory $campaignsDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Lof\SendGrid\Model\ResourceModel\Campaigns $resource
     * @param \Lof\SendGrid\Model\ResourceModel\Campaigns\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CampaignsInterfaceFactory $campaignsDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Lof\SendGrid\Model\ResourceModel\Campaigns $resource,
        \Lof\SendGrid\Model\ResourceModel\Campaigns\Collection $resourceCollection,
        array $data = []
    ) {
        $this->campaignsDataFactory = $campaignsDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve campaigns model with campaigns data
     * @return CampaignsInterface
     */
    public function getDataModel()
    {
        $campaignsData = $this->getData();

        $campaignsDataObject = $this->campaignsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $campaignsDataObject,
            $campaignsData,
            CampaignsInterface::class
        );

        return $campaignsDataObject;
    }
}
