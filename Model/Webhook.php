<?php
namespace Lof\SendGrid\Model;

use Magento\Backend\App\Action\Context;
use Lof\SendGrid\Api\WebhookInterface;

class Webhook implements WebhookInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Lof\SendGrid\Model\EventFactory $eventFactory,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->logger = $logger;
        $this->eventfactory = $eventFactory;
        $this->request = $request;
    }
    public function getDataWebhook()
    {
        $data = file_get_contents("php://input");
        $events = json_decode($data);
        foreach ($events as $item) {
            $event = $this->eventfactory->create();
            $event->setEmails($item->email);
            $event->setTime(date('m/d/Y H:i:s', $item->timestamp));
            $event->setEvent($item->event);
            $event->setCategory($item->category);
            $event->save();
        }
    }
}
