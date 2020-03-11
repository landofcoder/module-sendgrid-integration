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

namespace Lof\SendGrid\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CampaignsRepositoryInterface
 *
 * @package Lof\SendGrid\Api
 */
interface CampaignsRepositoryInterface
{

    /**
     * Save Campaigns
     * @param \Lof\SendGrid\Api\Data\CampaignsInterface $campaigns
     * @return \Lof\SendGrid\Api\Data\CampaignsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Lof\SendGrid\Api\Data\CampaignsInterface $campaigns
    );

    /**
     * Retrieve Campaigns
     * @param string $campaignsId
     * @return \Lof\SendGrid\Api\Data\CampaignsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($campaignsId);

    /**
     * Retrieve Campaigns matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\SendGrid\Api\Data\CampaignsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Campaigns
     * @param \Lof\SendGrid\Api\Data\CampaignsInterface $campaigns
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Lof\SendGrid\Api\Data\CampaignsInterface $campaigns
    );

    /**
     * Delete Campaigns by ID
     * @param string $campaignsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($campaignsId);
}
