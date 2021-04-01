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
 * Class AuthenticationMethod
 *
 * @package Lof\SendGrid\Model\Config\Source
 */
class AuthenticationMethod implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'Not require', 'label' => __('Not require')],['value' => 'Login/Password', 'label' => __('Login/Password')],['value' => 'CRAM-MD5', 'label' => __('CRAM-MD5')]];
    }

    public function toArray()
    {
        return ['Not require' => __('Not require'), 'Login/Password' => __('Login/Password'), 'CRAM-MD5' => __('CRAM-MD5')];
    }
}

