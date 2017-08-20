<?php

namespace Wm21w\Palletways\Observer;

class Palletways implements \Magento\Framework\Event\ObserverInterface
{


    protected $logger;
    protected $order;
    protected $file;
    protected $cav = [];
    protected $_directoryList;
    protected $apiUrl = 'https://portal.palletways.com/api/pc_psief_test?apikey=PWTESTKEY';
    protected $folder = '/importexport/palletways/';

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList)
    {
        $this->logger = $logger;
        $this->_directoryList = $directoryList;
    }


    public
    function execute(\Magento\Framework\Event\Observer $observer)
    {

        return;  // na razie nie używamy automatu po zapisaniu;

        $this->order = $observer->getData('order');
        $result = $this->setCsv()
            ->getFile()
            ->saveCsv()
            ->sendFile();


        $this->logger->addDebug($result);
        return $this;
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
        $path = $this->_directoryList->getPath('var') . $this->folder;
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
}