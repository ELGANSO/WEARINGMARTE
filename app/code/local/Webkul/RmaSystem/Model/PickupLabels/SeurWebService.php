<?php

class Webkul_RmaSystem_Model_PickupLabels_SeurWebService
{
    /**
     * @param int $rmaId
     * @param Mage_Sales_Model_Order $order
     * @param DateTime $date
     * @return bool
     */
    public function generateLabel(
        $rmaId,
        Mage_Sales_Model_Order $order,
        DateTime $date)
    {
        $rma = Mage::getModel('rmasystem/rma')->load($rmaId);

        $sourceAddress = $this->getSourceAddress($order, $rma);
        $destinationAddress = $this->getDestinationAddress();
        $innerXml = $this->getInnerXml($rmaId, $order, $sourceAddress, $destinationAddress, $date);
        $outerXml = $this->getOuterXml($innerXml);
        $response = $this->submitRequest($outerXml);

        if($response->isSuccess())
        {
            $this->saveLabel($rmaId, $order->getCustomerId(), $response->getLabelData());
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param int $rmaId
     * @param $customerId
     * @param string $labelData
     */
    public function saveLabel($rmaId, $customerId, $labelData)
    {
        $rmaLabelsDir = Mage::getBaseDir('media') . '/rmalabels';
        if(!file_exists($rmaLabelsDir))
        {
            mkdir($rmaLabelsDir);
        }

        $labelName = Mage::helper('rmasystem')->getLabelName($customerId, $rmaId);
        $labelPath = $rmaLabelsDir . '/' . $labelName. '.pdf';
        file_put_contents($labelPath, $labelData);
    }

    /**
     * @param string $xml
     * @return Webkul_RmaSystem_Model_PickupLabels_SeurWsResponse
     */
    public function submitRequest($xml)
    {
        $url = 'http://cit.seur.com/CIT-war/services/ImprimirECBWebService';
        $connection = curl_init($url);
        curl_setopt($connection, CURLOPT_POST, true);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $xml);

        static::log('Llamando a %s con la petición: %s', [ $url, $xml ]);
        $response = curl_exec($connection);
        static::log('Respuesta obtenida: %s', $response);

        return new Webkul_RmaSystem_Model_PickupLabels_SeurWsResponse($response);
    }

    /**
     * @param string $innerXml
     * @return string
     */
    public function getOuterXml($innerXml)
    {
        $user = Mage::getStoreConfig('carriers/i4seur/username');
        $password = Mage::getStoreConfig('carriers/i4seur/password');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>

<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:imp="http://localhost:7026/ImprimirECBWebService">
    <soapenv:Header />
    <soapenv:Body>
        <imp:impresionIntegracionOrderPDFConECBWS>
            <imp:in0>$user</imp:in0>
            <imp:in1>$password</imp:in1>
            <imp:in2><![CDATA[$innerXml]]></imp:in2>
            <imp:in3>filename.xml</imp:in3> <!-- filename.xml  -->
            <imp:in4>B83233346</imp:in4> <!-- vat orderer number , to be assigned by SEUR-->
            <imp:in5>28</imp:in5> <!-- Bussines unit code orderer , to be assigned by SEUR -->
            <imp:in6>-1</imp:in6> <!-- fix value  -->
            <imp:in7>orderer</imp:in7> <!-- name who invokes ws  -->
        </imp:impresionIntegracionOrderPDFConECBWS>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    public function getInnerXml(
        $rmaId,
        Mage_Sales_Model_Order $order,
        Webkul_RmaSystem_Model_PickupLabels_Address $sourceAddress,
        Webkul_RmaSystem_Model_PickupLabels_Address $destination,
        DateTime $date)
    {
        $weight = (int) $order->getWeight();
        $ccc = Mage::getStoreConfig('carriers/i4seur/ccc');
        $nif = Mage::getStoreConfig('carriers/i4seur/nif');
        $date = $date->format('dmyHis');
        $shipmentId = sprintf('RMA%s-%s', $rmaId, $order->getIncrementId());

        return <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
    <exp>
        <bulto>
            <!-- Number of packages and parcel weight-->
            <numeroBultos>1</numeroBultos>
            <peso_ci>$weight</peso_ci>
            <!-- Shipment -->
            <cccOrdenante>$ccc</cccOrdenante> <!-- Customer account number assigned by SEUR-->
            <numeroReferencia>$shipmentId</numeroReferencia> <!-- shipment id -->
            <servicio>1</servicio> <!-- service used by default 24h-->
            <producto>86</producto><!-- product used 86 to ecommerce returns and 87 pudo return-->
            <tipoPorte>Q</tipoPorte> <!--  transport charges   Q=order F=sender D=consignee C=cash-->
            <!-- Orderer  -->
            <nifOrdenante>$nif</nifOrdenante> <!-- vat orderer number -->
            <!-- sender  -->
            <nifOrigen>{$sourceAddress->getTaxvat()}</nifOrigen><!-- vat number -->
            <nombreContactoOrigen>{$sourceAddress->getContact()}</nombreContactoOrigen> <!-- contact name -->
            <tipoViaOrigen>CL</tipoViaOrigen> <!-- address type , fix value -->
            <calleOrigen>{$sourceAddress->getAddress()}</calleOrigen><!-- address name -->
            <tipoNumeroOrigen>N</tipoNumeroOrigen> <!-- address type number, fix value -->
            <numeroOrigen>{$sourceAddress->getNumber()}</numeroOrigen> <!-- address number -->
            <codigoPostalOrigen>{$sourceAddress->getPostCode()}</codigoPostalOrigen> <!-- zip code -->
            <poblacionOrigen>{$sourceAddress->getCity()}</poblacionOrigen> <!-- city name-->
            <telefonoRecogidaOrigen>{$sourceAddress->getPhone()}</telefonoRecogidaOrigen> <!-- sender phone -->
            <emailRecogidaOrigen>{$sourceAddress->getEmail()}</emailRecogidaOrigen><!-- sender mail -->
            <!-- consignee -->
            <nombreContactoDestino>{$destination->getContact()}</nombreContactoDestino><!-- contact name -->
            <tipoViaDestino>CL</tipoViaDestino><!-- address type , fix value -->
            <calleDestino>{$destination->getAddress()}</calleDestino><!-- address name -->
            <tipoNumeroDestino>N</tipoNumeroDestino><!-- address type number, fix value -->
            <numeroDestino>{$destination->getNumber()}</numeroDestino> <!-- address number -->
            <codigoPostalDestino>{$destination->getPostCode()}</codigoPostalDestino><!-- zip code -->
            <poblacionDestino>{$destination->getCity()}</poblacionDestino><!-- city name-->
            <paisDestino>ES</paisDestino><!-- country id iso2 -->
            <telefonoDestino>{$destination->getPhone()}</telefonoDestino><!-- phone -->
            <email_consignatario>{$destination->getEmail()}</email_consignatario>
            -<!-- mail to notifications -->
            <id_mercancia>400</id_mercancia> <!-- courier id type , fix value for international shipments only -->
            <!-- pick up -->
            <horaMananaDe>09:00</horaMananaDe> <!-- pick time morning from  -->
            <horaMananaA>14:00</horaMananaA><!-- morning to  -->
            <horaTardeDe>17:00</horaTardeDe><!-- evening from  -->
            <horaTardeA>21:00</horaTardeA><!-- evening to  -->
            <diaRecogida>$date</diaRecogida> <!-- ddmmyyhhmmss -->
            <observaciones /><!-- free remarks  -->
            <informacion_comp /><!-- free remarks  -->
            <tipoRecogida>Y</tipoRecogida><!-- pick up type , sender address= Y or PUDO = P available oct 15-->
            <enviar_email>S</enviar_email> <!-- mail to sender pdf codebar label S or N -->
        </bulto>
    </exp>
</root>
XML;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Webkul_RmaSystem_Model_Rma $rma
     * @return Webkul_RmaSystem_Model_PickupLabels_Address
     */
    public function getSourceAddress(Mage_Sales_Model_Order $order, Webkul_RmaSystem_Model_Rma $rma)
    {
        $taxvat = $order->getCustomerTaxvat();
        $contact = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        $address = $rma->getPickupAddress();
        $number = $rma->getPickupNumber();
        $postCode = $rma->getPickupPostcode();
        $city = $rma->getPickupCity();
        $phone = $rma->getPickupPhone();
        $email = $order->getCustomerEmail();
        $region = $rma->getPickupRegion();

        return new Webkul_RmaSystem_Model_PickupLabels_Address(
            $taxvat, $contact, $address, $number,
            $postCode, $city, $phone, $email, $region);
    }

    /**
     * @return Webkul_RmaSystem_Model_PickupLabels_Address
     */
    public function getDestinationAddress()
    {
        $contact = $this->getConfig('contact');
        $address = $this->getConfig('address');
        $number = $this->getConfig('number');
        $postCode = $this->getConfig('postcode');
        $city = $this->getConfig('city');
        $phone = $this->getConfig('phone');
        $email = $this->getConfig('email');
        $region = $this->getConfig('region');

        return new Webkul_RmaSystem_Model_PickupLabels_Address(
            null, $contact, $address, $number,
            $postCode, $city, $phone, $email, $region);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getConfig($name)
    {
        return Mage::getStoreConfig("rmasystem/seur/$name");
    }

    /**
     * @param string $pickupId
     */
    public function cancelPickup($pickupId)
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>

<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cre="http://crearRecogida.servicios.webseur">
   <soapenv:Header/>
   <soapenv:Body>
      <cre:anularRecogida>
         <cre:in0/>
         <cre:in1>$pickupId</cre:in1>
         <cre:in2>onticAnula</cre:in2>
         <cre:in3>onticAnula</cre:in3>
      </cre:anularRecogida>
   </soapenv:Body>
</soapenv:Envelope>'
XML;

        $connection = curl_init('https://ws.seur.com/webseur/services/WSCrearRecogida');
        curl_setopt($connection, CURLOPT_POST, true);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $xml);
        echo $xml;
        $response = curl_exec($connection);
        echo($response);die;
    }


    /**
     * @param string $message
     * @param array $args
     */
    private static function log($message, $args = [])
    {
        return Mage::helper('rmasystem')->log($message, $args);
    }
}