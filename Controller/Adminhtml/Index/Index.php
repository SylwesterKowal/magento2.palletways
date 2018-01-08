<?php

namespace Wm21w\Palletways\Controller\Adminhtml\Index;

use \Magento\Sales\Model\Order;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;
use  \Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Magento\Backend\App\Action
{
    /* @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */


    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        $this->_view->renderLayout();
    }
}

?>