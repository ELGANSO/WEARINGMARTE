<?php

class Webkul_RmaSystem_Model_PickupLabels_Address
{
    /** @var string|null */
    private $taxvat;
    /** @var string */
    private $contact;
    /** @var string */
    private $address;
    /** @var string */
    private $number;
    /** @var string */
    private $postCode;
    /** @var string */
    private $city;
    /** @var string */
    private $region;
    /** @var string */
    private $phone;
    /** @var string */
    private $email;

    /**
     * @param null|string $taxvat
     * @param string $contact
     * @param string $address
     * @param string $number
     * @param string $postCode
     * @param string $city
     * @param string $phone
     * @param string $email
     * @param $region
     */
    public function __construct($taxvat, $contact, $address, $number, $postCode, $city, $phone, $email, $region)
    {
        $this->taxvat = $taxvat;
        $this->contact = $contact;
        $this->address = $address;
        $this->number = $number;
        $this->postCode = $postCode;
        $this->city = $city;
        $this->phone = $phone;
        $this->email = $email;
        $this->region = $region;
    }

    /**
     * @return null|string
     */
    public function getTaxvat()
    {
        return $this->taxvat;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }
}