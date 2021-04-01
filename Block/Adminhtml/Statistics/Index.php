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
namespace Lof\SendGrid\Block\Adminhtml\Statistics;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Lof\SendGrid\Helper\Data;


class Index extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        DateTimeFactory $dateFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dateFactory = $dateFactory;
        $this->helper = $helper;
    }
    public function execute()
    {
    }
    public function getTimeNow() {
        return $this->_dateFactory->create()->gmtDate('Y-m-d');
    }
}
