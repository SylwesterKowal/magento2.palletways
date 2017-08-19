<?php
namespace Wm21w\Palletways\Observer;

class Palletways implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
     //$order= $observer->getData('order');
	 //$order->doSomething();

     return $this;
  }
}