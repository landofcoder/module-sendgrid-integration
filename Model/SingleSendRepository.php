<?php
/**
<<<<<<< HEAD
 * Copyright (c) 2019  Landofcoder
 *
=======
 * Copyright (c) 2020  Landofcoder
 * 
>>>>>>> create module settings, menu, model, database
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
<<<<<<< HEAD
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
=======
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
>>>>>>> create module settings, menu, model, database
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
use Lof\SendGrid\Model\ResourceModel\SingleSend\CollectionFactory as SingleSendCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Store\Model\StoreManagerInterface;
use Lof\SendGrid\Model\ResourceModel\SingleSend as ResourceSingleSend;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\SendGrid\Api\Data\SingleSendInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Lof\SendGrid\Api\Data\SingleSendSearchResultsInterfaceFactory;
use Lof\SendGrid\Api\SingleSendRepositoryInterface;

/**
 * Class SingleSendRepository
 *
 * @package Lof\SendGrid\Model
 */
class SingleSendRepository implements SingleSendRepositoryInterface
{

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataSingleSendFactory;

    protected $singleSendFactory;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;

    protected $resource;

    private $storeManager;

    protected $extensibleDataObjectConverter;
    protected $singleSendCollectionFactory;


    /**
     * @param ResourceSingleSend $resource
     * @param SingleSendFactory $singleSendFactory
     * @param SingleSendInterfaceFactory $dataSingleSendFactory
     * @param SingleSendCollectionFactory $singleSendCollectionFactory
     * @param SingleSendSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceSingleSend $resource,
        SingleSendFactory $singleSendFactory,
        SingleSendInterfaceFactory $dataSingleSendFactory,
        SingleSendCollectionFactory $singleSendCollectionFactory,
        SingleSendSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->singleSendFactory = $singleSendFactory;
        $this->singleSendCollectionFactory = $singleSendCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSingleSendFactory = $dataSingleSendFactory;
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
        \Lof\SendGrid\Api\Data\SingleSendInterface $singleSend
    ) {
        /* if (empty($singleSend->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $singleSend->setStoreId($storeId);
        } */
<<<<<<< HEAD

=======
        
>>>>>>> create module settings, menu, model, database
        $singleSendData = $this->extensibleDataObjectConverter->toNestedArray(
            $singleSend,
            [],
            \Lof\SendGrid\Api\Data\SingleSendInterface::class
        );
<<<<<<< HEAD

        $singleSendModel = $this->singleSendFactory->create()->setData($singleSendData);

=======
        
        $singleSendModel = $this->singleSendFactory->create()->setData($singleSendData);
        
>>>>>>> create module settings, menu, model, database
        try {
            $this->resource->save($singleSendModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the singleSend: %1',
                $exception->getMessage()
            ));
        }
        return $singleSendModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($singleSendId)
    {
        $singleSend = $this->singleSendFactory->create();
        $this->resource->load($singleSend, $singleSendId);
        if (!$singleSend->getId()) {
            throw new NoSuchEntityException(__('SingleSend with id "%1" does not exist.', $singleSendId));
        }
        return $singleSend->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->singleSendCollectionFactory->create();
<<<<<<< HEAD

=======
        
>>>>>>> create module settings, menu, model, database
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lof\SendGrid\Api\Data\SingleSendInterface::class
        );
<<<<<<< HEAD

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

=======
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
>>>>>>> create module settings, menu, model, database
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
<<<<<<< HEAD

=======
        
>>>>>>> create module settings, menu, model, database
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\SendGrid\Api\Data\SingleSendInterface $singleSend
    ) {
        try {
            $singleSendModel = $this->singleSendFactory->create();
            $this->resource->load($singleSendModel, $singleSend->getSinglesendId());
            $this->resource->delete($singleSendModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the SingleSend: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($singleSendId)
    {
        return $this->delete($this->get($singleSendId));
    }
}
<<<<<<< HEAD
=======

>>>>>>> create module settings, menu, model, database
