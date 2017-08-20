<?php

namespace Wm21w\Palletways\Plugin\Widget;


class Context
{
    protected $backendUrl;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl)
    {
        $this->backendUrl = $backendUrl;
    }

    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        $buttonList
    )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        $order_id = $request->getParam('order_id');
        if ($request->getFullActionName() == 'sales_order_view') {
            $buttonList->add(
                'custom_button',
                [
                    'label' => __('Palletways'),
                    'onclick' => 'window.open(\'' . $this->getCustomUrl($order_id) . '\',\'\',\'width=800,height=500\')',
                    'class' => 'ship'
                ]
            );
        }

        return $buttonList;
    }

    public function getCustomUrl($oId = '26')
    {
        $params = array('order_id' => $oId);
        return $this->backendUrl->getUrl("palletways/index/index", $params);
    }
}