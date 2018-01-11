<?php
    class Yeboyebo_MultiplePrices_Model_Observer
    {
        public function modifyPrice(Varien_Event_Observer $obs ){
        	
        	// Get the quote item
            $quote = $obs->getEvent()->getQuote();
            $item = $obs->getQuoteItem();
            $product_id=$item->getProductId();
            $_product=Mage::getModel('catalog/product')->load($product_id);
           
            if(isset($_POST['rebajado']) && !empty($_POST['rebajado']) && is_numeric((int)$_POST['rebajado'])){
            	$newprice= $this->get_price($_POST['rebajado']);
            }
            if(is_null($newprice))
            	$newprice=$_product->getPrice();

            if ($item->getParentItem()) {
             	$item = $item->getParentItem();
         	}

            // Set the custom price
            $item->setCustomPrice($newprice);
            $item->setOriginalCustomPrice($newprice);

            // Enable super mode on the product.
            $item->getProduct()->setIsSuperMode(true);

        }
        private function get_price($option){

        	$prices =  array('70','75','79');

        	if (isset($prices[(int)$option -1]))
        		$price = $prices[(int)$option -1];
        	else
        		$price = null;

        	return $price;
        }
    }

 ?>