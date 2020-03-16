<?php


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
