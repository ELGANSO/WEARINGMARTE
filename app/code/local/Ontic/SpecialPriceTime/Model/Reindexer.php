<?php

class Ontic_SpecialPriceTime_Model_Reindexer
{
    public function reindexPrices()
    {
        $productIds = [];

        foreach(Mage::app()->getWebsites() as $website)
        {
            foreach($website->getGroups() as $group)
            {
                foreach($group->getStores() as $store)
                {
                    $productIds = array_merge($productIds, $this->getPendingProductIds($store->getCode()));
                }
            }
        }

        $productIds = array_unique($productIds);

        foreach($productIds as $productId)
        {
            static::log(sprintf('Reindexando los precios del producto ID %s...', $productId));
        }

        Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);

        // Hack: Guardamos el stock_item de los productos para que se borre la caché de
        // Varnish de la página de producto y de la categoría
        foreach($productIds as $productId)
        {   
            static::log(sprintf('Borrando la caché Varnish del producto %s...', $productId));
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            $stockItem->save();
        }
    }

    private function getPendingProductIds($store)
    {
        $timeZone = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone'));
        $from = new DateTime('15 minutes ago', $timeZone);
        $to = new DateTime('now', $timeZone);

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setStore($store)
            ->addAttributeToFilter('special_from_date', ['gt' => $from->format('Y-m-d H:i:s')])
            ->addAttributeToFilter('special_from_date', ['lt' => $to->format('Y-m-d H:i:s')]);

        $productIds = [];
        foreach($products as $product)
        {
            static::log(sprintf(
                'El producto %s ha entrado en oferta en la fecha %s',
                $product->getId(),
                $product->getSpecialFromDate()));

            $productIds[] = $product->getId();
        }

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setStore($store)
            ->addAttributeToFilter('special_to_date', ['gt' => $from->format('Y-m-d H:i:s')])
            ->addAttributeToFilter('special_to_date', ['lt' => $to->format('Y-m-d H:i:s')]);

        foreach($products as $product)
        {
            static::log(sprintf(
                'El producto %s ha dejado de estar en oferta en la fecha %s',
                $product->getId(),
                $product->getSpecialToDate()));

            $productIds[] = $product->getId();
        }

        return $productIds;
    }

    private static function log($message)
    {
        Mage::log($message, null, 'ontic_specialpricetime.log', true);
    }
}
