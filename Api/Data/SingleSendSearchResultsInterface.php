<?php
/**
 * LandOfCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://www.landofcoder.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandOfCoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2020 Landofcoder (http://www.LandOfCoder.com/)
 * @license    http://www.LandOfCoder.com/LICENSE-1.0.html
 */

namespace Lof\SendGrid\Api\Data;

/**
 * Interface SingleSendSearchResultsInterface
 *
 * @package Lof\SendGrid\Api\Data
 */
interface SingleSendSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get SingleSend list.
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface[]
     */
    public function getItems();

    /**
     * Set entity_id list.
     * @param \Lof\SendGrid\Api\Data\SingleSendInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
