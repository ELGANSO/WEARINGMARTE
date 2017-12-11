<?php

class Aplazame_Aplazame_Api_BusinessModel_Article
{
    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public static function createFromProduct(Mage_Catalog_Model_Product $product)
    {
        $article = array(
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => substr($product->getDescription(), 0, 255),
            'url' => $product->getProductUrl(),
            'image_url' => $product->getImageUrl(),
        );

        return $article;
    }
}
