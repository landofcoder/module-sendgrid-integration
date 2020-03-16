<?php


namespace Lof\SendGrid\Model\SingleSend;

use Lof\SendGrid\Model\ResourceModel\SingleSend\CollectionFactory;
use Lof\SendGrid\Model\VersionsFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 *
 * @package Lof\SendGrid\Model\SingleSend
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $dataPersistor;

    protected $loadedData;
    protected $collection;
    private $version;


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
        VersionsFactory $versionsFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->version = $versionsFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
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
        $version = $this->version->getCollection()->addFieldToFilter('version_id', $model->getTemplateVersion())->getData();
        $this->version->load($version['0']['id']);
        $this->loadedData[$model->getId()] = $model->getData();
        $this->loadedData[$model->getId()]['html_content'] = $this->version->getHtmlContent();
        $this->dataPersistor->clear('lof_sendgrid_singlesend');
        return $this->loadedData;
    }
}
