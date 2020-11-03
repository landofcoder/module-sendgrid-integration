<?php


namespace Lof\SendGrid\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class SingleSendActions
 *
 * @package Lof\SendGrid\Ui\Component\Listing\Column
 */
class SingleSendActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_DELETE = 'lof_sendgrid/singlesend/delete';
    const URL_PATH_DUPLICATE = 'lof_sendgrid/singlesend/duplicate';
    const URL_PATH_EDIT = 'lof_sendgrid/singlesend/edit';
    protected $urlBuilder;
    const URL_PATH_PREVIEW = 'lof_sendgrid/singlesend/preview';

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "%1"', $item['name']),
                                'message' => __('Are you sure you wan\'t to delete a "%1" record?', $item['name'])
                            ]
                        ],
                        'duplicate' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DUPLICATE,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Duplicate'),
                            'confirm' => [
                                'title' => __('Duplicate "%1"', $item['name']),
                                'message' => __('Are you sure you wan\'t to duplicate a "%1" record?', $item['name'])
                            ]
                        ],
                        'preview' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_PREVIEW,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'target'=>'_blank',
                            'label' => __('Preview')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
