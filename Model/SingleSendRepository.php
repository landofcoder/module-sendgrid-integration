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

namespace Lof\SendGrid\Model;

use Lof\SendGrid\Api\Data\SingleSendInterface;
use Lof\SendGrid\Api\SingleSendRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Model\Data
 */
class SingleSendRepository implements SingleSendRepositoryInterface
{
    protected $resource;
    protected $modelFactory;
    protected $collectionFactory;
    protected $searchResultsFactory;
    protected $dataObjectHelper;
    protected $dataObjectProcessor;
    protected $dataFactory;
    protected $extensionAttributesJoinProcessor;
    private $storeManager;
    private $collectionProcessor;
    protected $_resource;
    protected $jsonHelper;

    /**
     * @var UserContextInterface
     */
    private $userContext;
    /**
     * @var AuthorizationInterface
     */
    private $authorization;
    /**
     * @param \Lof\SendGrid\Model\ResourceModel\SingleSend $resource
     * @param \Lof\SendGrid\Model\SingleSendFactory $modelFactory
     * @param \Lof\SendGrid\Api\Data\SingleSendInterfaceFactory $dataFactory
     * @param \Lof\SendGrid\Model\ResourceModel\SingleSend\CollectionFactory $collectionFactory,
     * @param \Lof\SendGrid\Api\Data\SingleSendSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Lof\SendGrid\Model\ResourceModel\SingleSend $resource,
        \Lof\SendGrid\Model\SingleSendFactory $modelFactory,
        SingleSendInterface $dataFactory,
        \Lof\SendGrid\Model\ResourceModel\SingleSend\CollectionFactory $collectionFactory,
        \Lof\SendGrid\Api\Data\SingleSendSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        ResourceConnection $Resource,
        Data $jsonHelper
    ) {
        $this->resource = $resource;
        $this->_resource = $Resource;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFactory = $dataFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->jsonHelper = $jsonHelper;
    }
    /**
     * {@inheritdoc}
     */
    public function save(
        SingleSendInterface $singleSend
    ) {
        try {
            $model = $this->modelFactory->create();
            $id = $singleSend->getEntityId();
            $model = $model->load($id);
            $data = [
                'singlesend_id' => $singleSend->getSinglesendId(),
                'name' => $singleSend->getName(),
                'create_date' => $singleSend->getCreateDate(),
                'update_date' => $singleSend->getUpdateDate(),
                'status' => $singleSend->getStatus(),
                'send_at' => $singleSend->getSendAt(),
                'sender_id' => $singleSend->getSenderId(),
                'suppression_group_id' => $singleSend->getSuppressionGroupId(),
                'list_ids' => $singleSend->getListIds()
            ];
            $model->setData($data);
            $model->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the single send: %1',
                $exception->getMessage()
            ));
        }
        return $singleSend;
    }

    /**
     * {@inheritdoc}
     */
    public function get($singlesendId)
    {
        $singleSend = $this->modelFactory->create();
        $this->resource->load($singleSend, $singlesendId);
        if (!$singleSend->getId()) {
            throw new NoSuchEntityException(__('Single Send with id "%1" does not exist.', $singlesendId));
        }
        return $singleSend;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }

        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        SingleSendInterface $singleSend
    ) {
        try {
            $this->resource->delete($singleSend);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Single Send: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($singlesendId)
    {
        return $this->delete($this->getById($singlesendId));
    }
}
