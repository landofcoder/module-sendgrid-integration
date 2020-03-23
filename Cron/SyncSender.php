<?php
/**
 * Copyright (c) 2020  Landofcoder
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
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
            }
            else {
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
