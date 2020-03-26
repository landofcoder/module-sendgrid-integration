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
            if ($item->mc_stats == "singlesend") {
                $event = $this->eventfactory->create();
                $event->setEmails($item->email);
                $event->setTimestamp(date('m/d/Y H:i:s', $item->timestamp));
                $event->setEvent($item->event);
                $event->setMcStats($item->mc_stats);
                $event->setPhaseId($item->phase_id);
                $event->setSendAt(date('m/d/Y H:i:s', $item->send_at));
                $event->setSgEventId($item->sg_event_id);
                $event->setSgMessageId($item->sg_message_id);
                $event->setSgTemplateId($item->sg_template_id);
                $event->setSgTemplateName($item->sg_template_name);
                $event->setSinglesendId($item->singlesend_id);
                $event->setTemplateId($item->template_id);
                if (isset($item->ip)) {
                    $event->setIp($item->ip);
                }
                if (isset($item->useragent)) {
                    $event->setUseragent($item->useragent);
                }
                if (isset($item->category)) {
                    $event->setCategory($item->category);
                }
                $event->save();
            }
        }
    }
}
