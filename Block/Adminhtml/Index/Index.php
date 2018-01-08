<?php

namespace Wm21w\Palletways\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Widget\Container
{
    protected $request;
    protected $item;
    protected $billitem;
    protected $objectManager;
    protected $_moduleHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context, array $data = [], \Magento\Framework\App\Request\Http $request,
        \Wm21w\Palletwazs\Helper\Data $moduleHelper
    )
    {
        $this->request = $request;
        parent::__construct($context, $data);

        $this->_moduleHelper = $moduleHelper;
    }

    public function getPalletwaysDecodeJson($apiUrl)
    {
        $json = file_get_contents($apiUrl);
        $decode = json_decode($json);
        return $decode;
    }

    public function getRedirectPalletways($url, $lang)
    {
        header('location:' . $url . '?setlang=' . $lang);
    }

    public function getObjectManager()
    {
        return $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getOrder()
    {
        return $this->getObjectManager()->create('Magento\Sales\Model\Order')->load($this->request->getParam('order_id'));
    }

    public function getOrderItems()
    {
        $items = $this->getOrder()->getAllItems();
        $l = 0;
        foreach ($items as $i) {
            // \Zend_Debug::dump($i->debug()); die;
            $this->item .= '<Product>';
            $this->item .= '<LineNo>' . $l . '</LineNo>';
            $this->item .= '<Description>' . $i->getName() . '</Description>';
            $this->item .= '<Quantity>' . round($i->getQtyOrdered()) . '</Quantity>';
            $this->item .= '<Code>' . $i->getSku() . '</Code>';
            $this->item .= '</Product>';

            $l++;
        }
        return $this->item;
    }

    private function getProductsWeight()
    {
        $weight = 0;
        foreach ($this->getOrder()->getAllItems() as $item) {
            if ($hasParent = $item->getParentItemId()) {
                continue;
            }
            $weight = $weight + $item->getWeight();
        }
        if ((int)$weight > 0) {
            return (int)$weight;
        } else {
            return 1;
        }
    }

    public function getBillUnit($weight)
    {
        if ($weight <= 150) {
            $this->billitem .= '<BillUnit>';
            $this->billitem .= '<Type>MQP</Type>';
            $this->billitem .= '<Amount>1</Amount>';
            $this->billitem .= '</BillUnit>';
        } else if ($weight > 150 && $weight <= 250) {
            $this->billitem .= '<BillUnit>';
            $this->billitem .= '<Type>QP</Type>';
            $this->billitem .= '<Amount>1</Amount>';
            $this->billitem .= '</BillUnit>';
        } else if ($weight > 250 && $weight <= 550) {
            $this->billitem .= '<BillUnit>';
            $this->billitem .= '<Type>HP</Type>';
            $this->billitem .= '<Amount>1</Amount>';
            $this->billitem .= '</BillUnit>';
        } else if ($weight > 550 && $weight <= 750) {
            $this->billitem .= '<BillUnit>';
            $this->billitem .= '<Type>LP</Type>';
            $this->billitem .= '<Amount>1</Amount>';
            $this->billitem .= '</BillUnit>';
        } else if ($weight > 750 && $weight <= 1200) {
            $this->billitem .= '<BillUnit>';
            $this->billitem .= '<Type>FP</Type>';
            $this->billitem .= '<Amount>1</Amount>';
            $this->billitem .= '</BillUnit>';
        }
        return $this->billitem;
    }

    public function getCreateConsignment()
    {
        if (!$this->_moduleHelper->isEnabled()) return;

        $helper = $this->getObjectManager()->create('Magento\Framework\Pricing\Helper\Data');
        $amount = $helper->currency(number_format($this->getOrder()->getGrandTotal()), false, false);
        $weight = $this->getProductsWeight();
        $billUnit = $this->getBillUnit($weight);

        $incrementId = $this->getOrder()->getIncrementId();
        $shippingAddress = $this->getOrder()->getShippingAddress();

        $data = '<?xml version="1.0" encoding="UTF-8"?>
					<Manifest>
					  <Date>' . date('Y-m-d') . '</Date>
					  <Time>' . date("H:i:s") . '</Time>
					  <Confirm>no</Confirm>
					  <Depot>
						<Account>
						  <Consignment>
							<Type>D</Type>
							<ImportID>' . $incrementId . '</ImportID>
							<Number>' . $incrementId . '</Number>
							<Reference>' . $incrementId . '</Reference>
							<Lifts>1</Lifts>
							<Weight>' . $weight . '</Weight>
							<Handball>true</Handball>
							<TailLift>true</TailLift>
							<BookInRequest>true</BookInRequest>
							<BookInInstructions></BookInInstructions>
							<ManifestNote></ManifestNote>
							<CollectionDate></CollectionDate>
							<DeliveryDate></DeliveryDate>
							<DeliveryTime></DeliveryTime>
							<Service>
							  <Type>Delivery</Type>
							  <Code>B</Code>
							  <Surcharge>B</Surcharge>
							</Service>
							<Address>
							  <Type>Delivery</Type>
							  <ContactName>' . $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname() . '</ContactName>
							  <Telephone>' . $shippingAddress->getTelephone() . '</Telephone>
							  <Fax>' . $shippingAddress->getFax() . '</Fax>
							  <CompanyName>' . $shippingAddress->getCompany() . '</CompanyName>
							  <Line>' . implode(', ', $shippingAddress->getStreet()) . '</Line>
							  <Line></Line>
							  <Town>' . $shippingAddress->getTown() . '</Town>
							  <County></County>
							  <PostCode>' . $shippingAddress->getPostcode() . '</PostCode>
							  <Country>' . $shippingAddress->getCountryId() . '</Country>
							</Address>
							<Return>
							  <Type>EU</Type>
							  <Amount>5</Amount>
							</Return>
							<Return>
							  <Type>EG</Type>
							  <Amount>1</Amount>
							</Return>
							<Pallet></Pallet>
							' . $billUnit . '
							<ClientUnit>
							  <Type>TMQP</Type>
							  <Amount>6</Amount>
							</ClientUnit>
							<ClientUnit>
							  <Type>TQP</Type>
							  <Amount>3</Amount>
							</ClientUnit>
							<ClientUnit>
							  <Type>TFP</Type>
							  <Amount>7</Amount>
							</ClientUnit>
							<ClientUnit>
							  <Type>FP</Type>
							  <Amount>1</Amount>
							</ClientUnit>
							<NotificationSet>
							  <SysGroup></SysGroup>
							  <SysGroup></SysGroup>
							  <SMSNumber>' . $shippingAddress->getTelephone() . '</SMSNumber>
							  <Email>' . $shippingAddress->getEmail() . '</Email>
							</NotificationSet>
							<CashPayment>
							  <Amount>' . $amount . '</Amount>
							  <Method>BD</Method>
							  <CollectFrom>Delivery</CollectFrom>
							  <FreeText></FreeText>
							</CashPayment>
							' . $this->getOrderItems() . '
						  </Consignment>
						</Account>
					  </Depot>
					</Manifest>';

        $apikey = $this->_moduleHelper->getApiKey();
//        var_dump($apikey);
        $data = array('apikey' => $apikey, 'inputformat' => 'xml', 'outputformat' => 'json', 'data' => $data, 'commit' => 'true');
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            )
        );

        $context = stream_context_create($options);

        try {
            $result = file_get_contents('https://api.palletways.com/createconsignment', false, $context);

            if (isset($result->Detail->ImportDetail[0]['TrackingID'])) {

                $this->order = $this->objectManager->create('\Magento\Sales\Model\Order')->load($this->request->getParam('order_id'));

                $this->order->setPalletconnectid($result->Detail->ImportDetail[0]['TrackingID']);
                $this->order->save();

            } else {
//                var_dump($result);
            }
        } catch (Exception $e) {
            echo 'Exception: ', $e->getMessage(), "\n";
        }
        return $result;
    }
}