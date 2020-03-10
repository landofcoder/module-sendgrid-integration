<?php

namespace Lof\SendGrid\Observer;

class GetContact implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $displayText = $observer->getData();
        var_dump($displayText);
        die;
        return $this;
    }
}