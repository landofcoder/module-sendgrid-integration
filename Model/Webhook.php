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
            $event->setTimestamp(date('m/d/Y H:i:s', $item->timestamp));
            $event->setEvent($item->event);
            if(isset($item->phase_id)) {
                $event->setPhaseId($item->phase_id);
            }
            if(isset($item->send_at)) {
                $event->setSendAt(date('m/d/Y H:i:s', $item->send_at));
            }
            if (isset($item->sg_event_id)) {
                $event->setSgEventId($item->sg_event_id);
            }
            if (isset($item->sg_message_id)) {
                $event->setSgMessageId($item->sg_message_id);
            }
            if (isset($item->sg_template_id)) {
                $event->setSgTemplateId($item->sg_template_id);
            }
            if (isset($item->sg_template_name)) {
                $event->setSgTemplateName($item->sg_template_name);
            }
            if (isset($item->singlesend_id)) {
                $event->setSinglesendId($item->singlesend_id);
            }
            if (isset($item->template_id)) {
                $event->setTemplateId($item->template_id);
            }
            if (isset($item->mc_stats)) {
                $event->setMcStats($item->mc_stats);
            }
            if (isset($item->ip)) {
                $event->setIp($item->ip);
            }
            if (isset($item->useragent)) {
                $event->setUseragent($item->useragent);
            }
            if (isset($item->category)) {
                $event->setCategory(json_encode($item->category));
            }
            $event->save();
        }
    }
}
