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

namespace Lof\SendGrid\Model\Config\Source;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Framework\App\Action\Context;

/**
 * Class SubscribeList
 *
 * @package Lof\SendGrid\Model\Config\Source
 */
class SubscribeList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var CollectionFactory
     */
    private $_collection;

    public function __construct(
        Context $context,
        CollectionFactory $collection
    ) {
        $this->context = $context;
        $this->_collection = $collection;
    }
    public function toOptionArray()
    {
        $options = [];
        $list = $this->_collection->create();
        foreach ($list as $item) {
            $options[] = [
                'label' => __($item->getSubscriberGroupName()),
                'value' => $item->getSubscriberGroupId(),
            ];
        }
        return $options;
    }
}
