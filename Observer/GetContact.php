<?php

namespace Lof\SendGrid\Observer;

use Lof\SendGrid\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class GetContact implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        ManagerInterface $manager,
        \Lof\SendGrid\Model\AddressBookFactory $addressBookFactory,
        DateTimeFactory $dateFactory,
        Data $helper

    ) {
        $this->addressBook = $addressBookFactory;
        $this->_messageManager = $manager;
        $this->helper = $helper;
        $this->_dateFactory = $dateFactory;

    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $group = $this->helper->getSendGridConfig('general', 'other_group');
        $info = $observer->getRequest()->getParams();
        $collection = $this->addressBook->create()->getCollection()->addFieldToFilter('email_address',$info['email']);
        if(count($collection) == 0) {
            $addressBook = $this->addressBook->create();
            $addressBook->setFirstname($info['name'])->setEmailAddress($info['email'])->setIsSubscribed('0')->setSourceFrom('Contact')->setCreatedAt($this->_dateFactory->create()->gmtDate())->setIsSync('0')->setGroupId($group);
            $addressBook->save();
        }
        return $this;
    }
}
