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
use Lof\SendGrid\Model\SubscriberFactory;
use Lof\SendGrid\Model\UnSubscriberFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class SubscriberAndUnsubscriber extends \Magento\Backend\App\Action
{
    protected $helper;
    /**
     * @var ManagerInterface
     */
    private $_messageManager;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;
    /**
     * @var SubscriberFactory
     */
    private $_subscriber;
    /**
     * @var UnSubscriberFactory
     */
    private $_unsubscriber;

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
        SubscriberFactory $subscriberFactory,
        UnSubscriberFactory $unSubscriberFactory,
        DateTimeFactory $dateFactory,
        ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->_subscriber = $subscriberFactory;
        $this->_unsubscriber = $unSubscriberFactory;
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
        $subscribers_groups = $this->helper->getAllList();
        $subscribers_groups = get_object_vars($subscribers_groups)['result'];
        foreach ($subscribers_groups as $subscribers_group) {
            $model = $this->_subscriber->create();
            $exits = $model->getCollection()->addFieldToFilter('subscriber_group_id',$subscribers_group->id)->getData();
            if(count($exits) == 0) {
                $model->setSubscriberGroupId($subscribers_group->id)
                    ->setSubscriberGroupName($subscribers_group->name)
                    ->setSubscriberCount($subscribers_group->contact_count);
                $model->save();
            }
            else {
                $model->load($exits['0']['id']);
                $model->setSubscriberGroupId($subscribers_group->id)
                    ->setSubscriberGroupName($subscribers_group->name)
                    ->setSubscriberCount($subscribers_group->contact_count);
                $model->save();
            }
        }
        $unsubscribers_groups = $this->helper->getUnsubscriberGroup();
        foreach ($unsubscribers_groups as $unsubscribers_group) {
            $model = $this->_unsubscriber->create();
            $exits = $model->getCollection()->addFieldToFilter('unsubscriber_group_id',$unsubscribers_group->id)->getData();
            if(count($exits) == 0) {
                $model->setUnsubscriberGroupId($unsubscribers_group->id)
                    ->setUnsubscriberGroupName($unsubscribers_group->name)
                    ->setUnsubscriberCount($unsubscribers_group->unsubscribes);
                $model->save();
            }
            else {
                $model->load($exits['0']['id']);
                $model->setUnsubscriberGroupId($unsubscribers_group->id)
                    ->setUnsubscriberGroupName($unsubscribers_group->name)
                    ->setUnsubscriberCount($unsubscribers_group->unsubscribes);
                $model->save();
            }
        }
    }
}
