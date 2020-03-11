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

namespace Lof\SendGrid\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Lof\SendGrid\Model\ResourceModel\Campaigns\CollectionFactory as CampaignsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Store\Model\StoreManagerInterface;
use Lof\SendGrid\Model\ResourceModel\Campaigns as ResourceCampaigns;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\SendGrid\Api\Data\CampaignsInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Lof\SendGrid\Api\Data\CampaignsSearchResultsInterfaceFactory;
use Lof\SendGrid\Api\CampaignsRepositoryInterface;

/**
 * Class CampaignsRepository
 *
 * @package Lof\SendGrid\Model
 */
class CampaignsRepository implements CampaignsRepositoryInterface
{

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataCampaignsFactory;

    protected $campaignsFactory;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;

    protected $resource;

    private $storeManager;

    protected $extensibleDataObjectConverter;
    protected $campaignsCollectionFactory;


    /**
     * @param ResourceCampaigns $resource
     * @param CampaignsFactory $campaignsFactory
     * @param CampaignsInterfaceFactory $dataCampaignsFactory
     * @param CampaignsCollectionFactory $campaignsCollectionFactory
     * @param CampaignsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceCampaigns $resource,
        CampaignsFactory $campaignsFactory,
        CampaignsInterfaceFactory $dataCampaignsFactory,
        CampaignsCollectionFactory $campaignsCollectionFactory,
        CampaignsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->campaignsFactory = $campaignsFactory;
        $this->campaignsCollectionFactory = $campaignsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCampaignsFactory = $dataCampaignsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Lof\SendGrid\Api\Data\CampaignsInterface $campaigns
    ) {
        /* if (empty($campaigns->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $campaigns->setStoreId($storeId);
        } */

        $campaignsData = $this->extensibleDataObjectConverter->toNestedArray(
            $campaigns,
            [],
            \Lof\SendGrid\Api\Data\CampaignsInterface::class
        );

        $campaignsModel = $this->campaignsFactory->create()->setData($campaignsData);

        try {
            $this->resource->save($campaignsModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the campaigns: %1',
                $exception->getMessage()
            ));
        }
        return $campaignsModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($campaignsId)
    {
        $campaigns = $this->campaignsFactory->create();
        $this->resource->load($campaigns, $campaignsId);
        if (!$campaigns->getId()) {
            throw new NoSuchEntityException(__('Campaigns with id "%1" does not exist.', $campaignsId));
        }
        return $campaigns->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->campaignsCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lof\SendGrid\Api\Data\CampaignsInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\SendGrid\Api\Data\CampaignsInterface $campaigns
    ) {
        try {
            $campaignsModel = $this->campaignsFactory->create();
            $this->resource->load($campaignsModel, $campaigns->getCampaignsId());
            $this->resource->delete($campaignsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Campaigns: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($campaignsId)
    {
        return $this->delete($this->get($campaignsId));
    }
}
