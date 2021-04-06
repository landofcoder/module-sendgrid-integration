<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SendGrid
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
namespace Lof\SendGrid\Observer;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\AddressBookFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class GetContact
 * Lof\SendGrid\Observer
 */
class GetContact implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var AddressBookFactory
     */
    private $addressBook;
    /**
     * @var ManagerInterface
     */
    private $_messageManager;
    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;

    /**
     * GetContact constructor.
     * @param ManagerInterface $manager
     * @param AddressBookFactory $addressBookFactory
     * @param DateTimeFactory $dateFactory
     * @param Data $helper
     */
    public function __construct(
        ManagerInterface $manager,
        AddressBookFactory $addressBookFactory,
        DateTimeFactory $dateFactory,
        Data $helper
    ) {
        $this->addressBook = $addressBookFactory;
        $this->_messageManager = $manager;
        $this->helper = $helper;
        $this->_dateFactory = $dateFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $group = $this->helper->getSendGridConfig('general', 'other_group');
        $info = $observer->getRequest()->getParams();
        $collection = $this->addressBook->create()->getCollection()->addFieldToFilter('email_address', $info['email']);
        if (count($collection) == 0) {
            $addressBook = $this->addressBook->create();
            $addressBook->setFirstname($info['name'])
                ->setEmailAddress($info['email'])
                ->setIsSubscribed('0')
                ->setSourceFrom('Contact')
                ->setCreatedAt($this->_dateFactory->create()->gmtDate())
                ->setIsSync('0')
                ->setGroupId($group);
            $addressBook->save();
        }
        return $this;
    }
}
