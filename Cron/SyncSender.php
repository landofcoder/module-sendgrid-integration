<?php
/**
 * LandOfCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://www.landofcoder.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandOfCoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2020 Landofcoder (http://www.LandOfCoder.com/)
 * @license    http://www.LandOfCoder.com/LICENSE-1.0.html
 */

namespace Lof\SendGrid\Cron;

use Exception;
use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\SenderFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class SyncSender extends \Magento\Backend\App\Action
{
    protected $helper;
    /**
     * @var ManagerInterface
     */
    private $_messageManager;
    /**
     * @var SenderFactory
     */
    private $_sender;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $helper
     * @param DateTimeFactory $dateFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Data $helper,
        SenderFactory $senderFactory,
        DateTimeFactory $dateFactory,
        ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->_sender = $senderFactory;
        $this->_dateFactory = $dateFactory;
        $this->_messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $api_key = $this->helper->getSendGridConfig('general','api_key');
        $senders = $this->helper->getAllSenders($api_key);
        foreach ($senders as $sender) {
            if($sender && $sender->id){
                $model = $this->_sender->create();
                $exits = $model->getCollection()->addFieldToFilter('sender_id',$sender->id)->getData();
                if(count($exits) == 0) {
                    $model->setNickName($sender->nickname)
                        ->setSenderId($sender->id)
                        ->setFrom($sender->from->email)
                        ->setFromName($sender->from->name)
                        ->setReplyTo($sender->reply_to->email)
                        ->setAddress($sender->address)
                        ->setCity($sender->city)
                        ->setCountry($sender->country)
                        ->setVerified($sender->verified->status)
                        ->setUpdateAt($sender->updated_at)
                        ->setCreateAt($sender->created_at);
                    $model->save();
                }else {
                    $model->load($exits['0']['id']);
                    $model->setNickName($sender->nickname)
                        ->setFrom($sender->from->email)
                        ->setFromName($sender->from->name)
                        ->setReplyTo($sender->reply_to->email)
                        ->setAddress($sender->address)
                        ->setCity($sender->city)
                        ->setCountry($sender->country)
                        ->setVerified($sender->verified->status)
                        ->setUpdateAt($sender->updated_at)
                        ->setCreateAt($sender->created_at);
                    $model->save();
                }
            }
        }
    }
}
