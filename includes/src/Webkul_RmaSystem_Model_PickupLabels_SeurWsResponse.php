<?php

class Webkul_RmaSystem_Model_PickupLabels_SeurWsResponse
{
    /** @var boolean */
    private $success;
    /** @var string */
    private $message;
    /** @var string */
    private $labelData;

    public function __construct($rawResponse)
    {
        $xml = new DOMDocument();
        $xml->loadXML($rawResponse);

        // Obtenemos el mensaje
        $this->message = $xml->getElementsByTagName('mensaje')->item(0)->nodeValue;

        // Si el mensaje es "OK" es que la recogida se ha creado correctamente, si no
        // es que ha habido un error
        $this->success = ($this->message === 'OK');

        // Si la recogida ha ido bien, parsemos el PDF devuelto
        if($this->success)
        {
            $data = $xml->getElementsByTagName('PDF')->item(0)->nodeValue;
            $data = preg_replace('/\s+/', '', $data);
            $this->labelData = base64_decode($data);
        }
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getLabelData()
    {
        return $this->labelData;
    }
}