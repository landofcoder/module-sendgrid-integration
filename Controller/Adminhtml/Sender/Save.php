<?php


namespace Lof\SendGrid\Controller\Adminhtml\Sender;

use Exception;
use Lof\SendGrid\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class Save
 *
 * @package Lof\SendGrid\Controller\Adminhtml\SingleSend
 */
class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;
    /**
     * @var \Lof\SendGrid\Model\VersionsFactory
     */
    private $version;
    /**
     * @var Data
     */
    private $_helperdata;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\SendGrid\Helper\Data $helper,
        \Lof\SendGrid\Model\ResourceModel\Sender\CollectionFactory $collectionFactory,
        DateTimeFactory $dateFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->_helperdata = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->collection = $collectionFactory;
        $this->_dateFactory = $dateFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $api_key = $this->_helperdata->getSendGridConfig('general', 'api_key');
        if ($data) {
            $model = $this->_objectManager->create(\Lof\SendGrid\Model\Sender::class);
            $model->setData($data);
            $model->setVerified('0');
            $model->setUpdateAt($this->_dateFactory->create()->gmtDate());
            $model->setCreateAt($this->_dateFactory->create()->gmtDate());
            $nickname = $data['nick_name'];
            $from = $data['from'];
            $fromName = $data['from_name'];
            $replyTo = $data['reply_to'];
            $address = $data['address'];
            $city = $data['city'];
            $country = $data['country'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/senders",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\"nickname\":\"$nickname\",\"from\":{\"email\":\"$from\",\"name\":\"$fromName\"},\"reply_to\":{\"email\":\"$replyTo\"},\"address\":\"$address\",\"city\":\"$city\",\"country\":\"$country\"}",
                CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer $api_key",
                    "content-type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $collection = $this->collection->create()->addFieldToFilter('nick_name',$model->getNickName())->getData();
            if(count($collection) > 0) {
                $this->messageManager->addErrorMessage(__('You already have a sender identity with the same nickname.'));
                return $resultRedirect->setPath('*/*/');
            }
            if(isset(json_decode($response)->id)) {
                $model->setSenderId(json_decode($response)->id);
                try {
                    $model->save();
                    $this->messageManager->addSuccessMessage(__('You have created the Sender. Please check your email and verify it'));
                    $this->dataPersistor->clear('lof_sendgrid_senders');
                    return $resultRedirect->setPath('*/*/');
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Sender.'));
                }
            }
            else {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the Sender.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
