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
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->logger = $logger;
        $this->request = $request;
    }
    public function getDataWebhook()
    {
        $data = file_get_contents("php://input");
        $events = json_encode($data);
        $this->logger->critical('Webhook Data: ', [$events]);
    }
}
