<?php

namespace Wm21w\Palletways\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    protected $storeManager;
    protected $objectManager;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    )
    {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }


    public function isEnabled($storeId = null)
    {
        return $this->getConfigValue('palletways/general/enable', $storeId);
    }

    public function getApiKey($storeId = null)
    {
        return $this->getConfigValue('palletways/general/apikey', $storeId);
    }


    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCurrentUrl()
    {
        $model = $this->objectManager->get('Magento\Framework\UrlInterface');
        return $model->getCurrentUrl();
    }
}
