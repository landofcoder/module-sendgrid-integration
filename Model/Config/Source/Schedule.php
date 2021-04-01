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

use Magento\Framework\App\Action\Context;

/**
 * Class Schedule
 *
 * @package Lof\SendGrid\Model\Config\Source
 */
class Schedule implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Context
     */
    private $context;
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }
    public function toOptionArray()
    {
        return [
            1 => [
                'label' => 'Send Imediately',
                'value' => 1
            ],
            2  => [
                'label' => 'Specify a Date and Time',
                'value' => 2
            ]
        ];
    }
}
