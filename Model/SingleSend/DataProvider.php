<?php


namespace Lof\SendGrid\Model\SingleSend;

use Lof\SendGrid\Model\ResourceModel\SingleSend\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 *
 * @package Lof\SendGrid\Model\SingleSend
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var
     */
    protected $loadedData;
    /**
     * @var
     */
    protected $collection;


    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        $id = '';
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
            $id = $model->getEntityId();
        }
        $model = $this->collection->getNewEmptyItem();
        $model->load($id);
        $this->loadedData[$model->getId()] = $model->getData();
        if ($model->getData('list_ids')) {
            $this->loadedData[$model->getId()]['list_ids'] = json_decode($model->getData('list_ids'));
        }
        $this->dataPersistor->clear('lof_sendgrid_singlesend');
        return $this->loadedData;
    }
}
