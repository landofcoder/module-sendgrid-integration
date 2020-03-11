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

namespace Lof\SendGrid\Api\Data;

/**
 * Interface CampaignsInterface
 *
 * @package Lof\SendGrid\Api\Data
 */
interface CampaignsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const CAMPAIGNS = 'campaigns';
    const CAMPAIGNS_ID = 'campaigns_id';
    const UPDATE_DATE = 'update_date';
    const CREATE_DATE = 'create_date';
    const NAME = 'name';

    /**
     * Get campaigns_id
     * @return string|null
     */
    public function getCampaignsId();

    /**
     * Set campaigns_id
     * @param string $campaignsId
     * @return \Lof\SendGrid\Api\Data\CampaignsInterface
     */
    public function setCampaignsId($campaignsId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Lof\SendGrid\Api\Data\CampaignsInterface
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\SendGrid\Api\Data\CampaignsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Lof\SendGrid\Api\Data\CampaignsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\SendGrid\Api\Data\CampaignsExtensionInterface $extensionAttributes
    );

    /**
     * Get create_date
     * @return string|null
     */
    public function getCreateDate();

    /**
     * Set create_date
     * @param string $createDate
     * @return \Lof\SendGrid\Api\Data\CampaignsInterface
     */
    public function setCreateDate($createDate);

    /**
     * Get update_date
     * @return string|null
     */
    public function getUpdateDate();

    /**
     * Set update_date
     * @param string $updateDate
     * @return \Lof\SendGrid\Api\Data\CampaignsInterface
     */
    public function setUpdateDate($updateDate);

    /**
     * Get campaigns
     * @return string|null
     */
    public function getCampaigns();

    /**
     * Set campaigns
     * @param string $campaigns
     * @return \Lof\SendGrid\Api\Data\CampaignsInterface
     */
    public function setCampaigns($campaigns);
}
