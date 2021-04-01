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
 * Class ConnectionSecurity
 *
 * @package Lof\SendGrid\Model\Config\Source
 */
class ConnectionSecurity implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'None', 'label' => __('None')],['value' => 'SSL', 'label' => __('SSL')],['value' => 'TLS', 'label' => __('TLS')]];
    }

    public function toArray()
    {
        return ['None' => __('None'),'SSL' => __('SSL'),'TLS' => __('TLS')];
    }
}

