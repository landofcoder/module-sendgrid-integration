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

namespace Lof\SendGrid\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface SingleSendRepositoryInterface
 *
 * @package Lof\SendGrid\Api
 */
interface SingleSendRepositoryInterface
{

    /**
     * Save SingleSend
     * @param \Lof\SendGrid\Api\Data\SingleSendInterface $singleSend
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Lof\SendGrid\Api\Data\SingleSendInterface $singleSend
    );

    /**
     * Retrieve SingleSend
     * @param string $singlesendId
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($singlesendId);

    /**
     * Retrieve SingleSend matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\SendGrid\Api\Data\SingleSendSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete SingleSend
     * @param \Lof\SendGrid\Api\Data\SingleSendInterface $singleSend
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Lof\SendGrid\Api\Data\SingleSendInterface $singleSend
    );

    /**
     * Delete SingleSend by ID
     * @param string $singlesendId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($singlesendId);
}
