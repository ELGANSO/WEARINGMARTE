<?php

/**
 * Article.
 */
class Aplazame_Aplazame_BusinessModel_Article
{
    public static function crateFromOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        $productId = $orderItem->getProductId();
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($productId);
        $discounts = $product->getPrice() - $product->getFinalPrice();

        $aArticle = new self();
        $aArticle->id = $productId;
        $aArticle->name = $orderItem->getName();
        $aArticle->url = $product->getProductUrl();
        $aArticle->image_url = $product->getImageUrl();
        $aArticle->quantity = intval($orderItem->getQtyOrdered());
        $aArticle->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($orderItem->getPrice() + $discounts);
        $aArticle->description = substr($product->getDescription(), 0, 255);
        $aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat(self::getProductTaxRate($product));
        $aArticle->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($discounts);

        return $aArticle;
    }

    private static function getProductTaxRate(Mage_Catalog_Model_Product $product)
    {
        /** @var Mage_Tax_Model_Calculation $taxCalculation */
        $taxCalculation = Mage::getSingleton('tax/calculation');

        $store = Mage::app()->getStore();
        $request = $taxCalculation->getRateRequest(null, null, null, $store);
        $taxClassId = $product->getData('tax_class_id');

        return $taxCalculation->getRate($request->setProductClassId($taxClassId));
    }
}
