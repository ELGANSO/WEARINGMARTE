<?php

class Webkul_RmaSystem_Model_Sizeinfo
{
    /** @var int */
    private $productId;
    /** @var string */
    private $description;

    /**
     * @param int $productId
     * @param string $description
     * @return $this
     */
    public function load($productId, $description)
    {
        $this->productId = $productId;
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}