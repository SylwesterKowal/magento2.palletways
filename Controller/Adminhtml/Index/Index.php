<?php

namespace Wm21w\Palletways\Controller\Adminhtml\Index;

use \Magento\Sales\Model\Order;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;
use  \Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Magento\Backend\App\Action
{
    /* @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */

    protected $objectManager;
    protected $logger;
    protected $order;
    protected $file;
    protected $cav = [];
    protected $_directoryList;
    protected $apiKey = 'PWTESTKEY';
//    protected $apiUrl = 'https://portal.palletways.com/api/pc_psief_test?apikey=PALLETWAYSAPIKEY';
    protected $apiUrl = 'https://portal.palletways.com/api/testConSubmit?apikey=PALLETWAYSAPIKEY';
    protected $apiVerUrl = 'https://portal.palletways.com/api/version?apikey=PALLETWAYSAPIKEY';
    protected $apiStatusUrl = 'https://portal.palletways.com/api/testConStatus/PALLETWAYSID?apikey=PALLETWAYSAPIKEY';
    protected $apiLabelUrl = 'https://portal.palletways.com/api/getLabelsById/PALLETWAYSID?apikey=PALLETWAYSAPIKEY';
    protected $folder = '/importexport/palletways/';

    protected function init()
    {


        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();


//        $showTemplateHint =  $this->_scopeConfig->getValue('dev/debug/template_hints', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        if ($this->getConfigData('palletways_api_key')) {
//            $this->apiKey = $this->getConfigData('palletways_api_key');
//        }

        $this->apiUrl = str_replace('PALLETWAYSAPIKEY', $this->apiKey, $this->apiUrl);
        $this->apiVerUrl = str_replace('PALLETWAYSAPIKEY', $this->apiKey, $this->apiVerUrl);
        $this->apiStatusUrl = str_replace('PALLETWAYSAPIKEY', $this->apiKey, $this->apiStatusUrl);
        $this->apiLabelUrl = str_replace('PALLETWAYSAPIKEY', $this->apiKey, $this->apiStatusUrl);


        return $this;
    }

    public function execute()
    {
        $this->init();
        $request = $this->objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        $order_id = $request->getParam('order_id');

        $this->order = $this->objectManager->create('\Magento\Sales\Model\Order')->load($order_id);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $block = $this->_view->getLayout()->getBlock('palletways_block_adminhtml_index_index');
        $palletconnectid = $this->order->getPalletconnectid();
        if (!empty($palletconnectid)) {
            $block->assign([
                'api_key' => $this->apiKey,
                'order_id' => $this->order->getIncrementId(),
                'palletways_id' => $palletconnectid,
                'palletways_version' => $this->getPalletwaysVersion(),
                'palletways_status' => $this->getPalletwaysStatus($palletconnectid),
                'palletways_label' => str_replace('PALLETWAYSID', $palletconnectid, $this->apiLabelUrl)
            ]);

            $this->objectManager->create('\Psr\Log\LoggerInterface')->addDebug($palletconnectid);
        } else {
            $result = $this->setCsv()
                ->getFile()
                ->saveCsv()
                ->sendFile();

            $palletways = json_decode($result);
            $this->objectManager->create('\Psr\Log\LoggerInterface')->addDebug($palletways->id);

            if (isset($palletways->id) && !empty($palletways->id)) {
                $this->order->setPalletconnectid($palletways->id);
                $this->order->save();
            }
            $block->assign([
                'api_key' => $this->apiKey,
                'order_id' => $this->order->getIncrementId(),
                'palletways_id' => $palletways->id,
                'palletways_version' => $this->getPalletwaysVersion(),
                'palletways_status' => $this->getPalletwaysStatus($palletways->id),
                'palletways_label' => str_replace('PALLETWAYSID', $palletways->id, $this->apiLabelUrl)
            ]);
        }
        $this->_view->renderLayout();
    }

    private
    function saveCsv()
    {
        $fp = fopen($this->file, 'w');

        foreach ($this->cav as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        return $this;
    }

    private function getFile()
    {
        $base = $this->objectManager->get('Magento\Framework\App\Filesystem\DirectoryList')
            ->getPath('var');

        $path = $base . $this->folder;
        $this->file = $path . $this->order->getIncrementId() . '.csv';
        return $this;
    }

    private function sendFile()
    {
        $request = curl_init($this->apiUrl);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt(
            $request,
            CURLOPT_POSTFIELDS,
            array(
                'file' => '@' . realpath($this->file)
            ));

        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($request);
        curl_close($request);
        return $response;
    }

    private function setCsv()
    {
//        $billingAddress = $this->order->getBillingAddress();
        $shippingAddress = $this->order->getShippingAddress();

        $data = [];
        $data[] = [
            1 => '',
            2 => '',
            3 => $this->order->getIncrementId(),
            4 => '',
            5 => '',
            6 => '',
            7 => '',
            8 => '',
            9 => 'B010',
            10 => '',
            11 => '',
            12 => '',
            13 => $this->getProductsWeight(),
            14 => '',
            15 => '',
            16 => '',
            17 => '',
            18 => '',
            19 => '',
            20 => '',
            21 => '',
            22 => '',
            23 => '',
            24 => '',
            25 => '',
            26 => '',
            27 => '',
            28 => '',
            29 => '',
            30 => '',
            31 => '',
            32 => '',
            33 => '',
            34 => '',
            35 => '',
            36 => '',
            37 => '',
            38 => '',
            39 => '',
            40 => '',
            41 => '',
            42 => '',
            43 => '',
            44 => '',
            45 => '',
            46 => '',
            47 => '',
            48 => '',
            49 => 'A',
            50 => 'A',
            51 => '',
            52 => '',
            53 => '',
            54 => '',
            55 => '',
            56 => '',
            57 => '',
            58 => trim($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname()),
            59 => $shippingAddress->getTelephone(),
            60 => trim($shippingAddress->getCompany()),
            61 => implode(', ', $shippingAddress->getStreet()),
            62 => '',
            63 => '',
            64 => '',
            65 => '',
            66 => '',
            67 => '',
            68 => $shippingAddress->getCountryId(),
            69 => $shippingAddress->getPostcode(),
            70 => '',
            71 => '',
            72 => '',
            73 => '',
            74 => '',
            75 => '',
            76 => '',
            77 => '',
            78 => '',
            79 => '',
            80 => '',
            81 => '',
            82 => '',
            83 => '',
            84 => '',
            85 => '',
            86 => '',
            87 => '',
            88 => '',
            89 => '',
            90 => '',
            91 => '',
            92 => '',
            93 => '',
            94 => '',
            95 => '',
            96 => '',
            97 => '',
            98 => '',
            99 => '',
            100 => ''

        ];

        $this->cav = $data;
        return $this;
    }

    private function getProductsWeight()
    {
        $weight = 0;
        foreach ($this->order->getAllItems() as $item) {
            if ($hasParent = $item->getParentItemId()) {
                continue;
            }

            $weight = $weight + $item->getWeight();
        }
        return $weight;
    }

    private function getPalletwaysVersion()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->apiVerUrl);
        $result = curl_exec($ch);
        curl_close($ch);

        $ver = json_decode($result);
        return $ver->Version;
    }

    private function getPalletwaysStatus($id)
    {
        $this->apiStatusUrl = str_replace('PALLETWAYSID', $id, $this->apiStatusUrl);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->apiStatusUrl);
        $result = curl_exec($ch);
        curl_close($ch);

        $ver = json_decode($result, true);
        return $ver;
    }
}

?>