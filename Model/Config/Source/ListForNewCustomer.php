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

/**
 * Class ListForNewCustomer
 *
 * @package Lof\SendGrid\Model\Config\Source
 */
class ListForNewCustomer implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'Global List', 'label' => __('Global List')],['value' => 'All Customer', 'label' => __('All Customer')],['value' => 'Additional Subscribers', 'label' => __('Additional Subscribers')]];
    }

    public function toArray()
    {
        return ['Global List' => __('Global List'),'All Customer' => __('All Customer'),'Additional Subscribers' => __('Additional Subscribers')];
    }
}

