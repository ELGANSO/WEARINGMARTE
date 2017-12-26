<?php
	class Webkul_RmaSystem_Helper_Data extends Mage_Core_Helper_Abstract {
		protected static $egridImgDir = null;
	    protected static $egridImgURL = null;
	    protected static $egridImgThumb = null;
	    protected static $egridImgThumbWidth = null;
	    protected $_allowedExtensions = Array();
		public function __construct() {
	        self::$egridImgDir = Mage::getBaseDir("media") . DS;
	        self::$egridImgURL = Mage::getBaseUrl("media");
	        self::$egridImgThumb = "thumb/";
	        self::$egridImgThumbWidth = 100;
	    }
		public function getUrl()   	{
	    	return $this->_getUrl("rmasystem/guest");
		}
		public function getImageUrl($image_file) {
	        $url = false;
	        if (file_exists(self::$egridImgDir . self::$egridImgThumb . $this->updateDirSepereator($image_file)))
	            $url = self::$egridImgURL . self::$egridImgThumb . $image_file;
	        else
	            $url = self::$egridImgURL . $image_file;
	        return $url;
	    }
		public function getFileExists($image_file) {
	        $file_exists = false;
	        $file_exists = file_exists(self::$egridImgDir . $this->updateDirSepereator($image_file));
	        return $file_exists;
	    }
		public function getImageThumbSize($image_file) {
	        $img_file = $this->updateDirSepereator(self::$egridImgDir . $image_file);
	        if ($image_file == "" || !file_exists($img_file))
	            return false;
	        list($width, $height, $type, $attr) = getimagesize($img_file);
	        $a_height = (int) ((self::$egridImgThumbWidth / $width) * $height);
	        return Array("width" => self::$egridImgThumbWidth, "height" => $a_height);
	    }
		public function updateDirSepereator($path){
	        return str_replace("\\", DS, $path);
	    }
		function deleteFiles($image_file) {
	        $pass = true;
	        if (!unlink(self::$egridImgDir . $image_file))
	            $pass = false;
	        if (!unlink(self::$egridImgDir . self::$egridImgThumb . $image_file))
	            $pass = false;
	        return $pass;
	    }
        /**
         * @param $message
         * @param $arguments
         */
	    public function log($message, $arguments)
        {
            $rmaId = Mage::registry('rmasystem_approved_rma_id');
            if($rmaId)
            {
                $message = "[RMA$rmaId] $message";
            }
            Mage::log(vsprintf($message, $arguments), null, 'devoluciones.log', true);
        }
        /**
         * Obtiene todas las tallas en las que está disponible un producto configurable
         * @param Mage_Catalog_Model_Product $product
         * @return Webkul_RmaSystem_Model_Sizeinfo[]
         */
        public function getAvailableSizes(Mage_Catalog_Model_Product $product)
        {
            if($product->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            {
                // No es un producto configurable, así que no tiene tallas
                return [];
            }
            /** @var Mage_Catalog_Model_Product_Type_Configurable $configurableProduct */
            $configurableProduct = $product->getTypeInstance();
            $sizes = [];
            $childProducts = $configurableProduct
                ->getUsedProductCollection()
                ->addAttributeToSelect(['size'])
                ->addFilterByRequiredOptions()
                ->setStore($product->getStoreId());
            /** @var Mage_Catalog_Model_Product $childProduct */
            foreach ($childProducts as $childProduct)
            {
                /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
                $stockItem = $childProduct['stock_item'];
                if ($stockItem->getIsInStock())
                {
                    $productId = $childProduct->getId();
                    /** @noinspection PhpParamsInspection */
                    $description = $childProduct->getAttributeText('size');
                    $sizes[] = Mage::getModel('rmasystem/sizeinfo')->load($productId, $description);
                }
            }
            return $sizes;
        }
        /**
         * Devuelve todas las tallas disponibles para el producto, salvo la que seleccionó
         * originalmente en la compra
         * @param Mage_Sales_Model_Order_Item $item
         * @return Webkul_RmaSystem_Model_Sizeinfo[]
         */
        public function getOtherAvailableSizes(Mage_Sales_Model_Order_Item $item)
        {
            if(!($childProduct = $this->getLinkedSimpleItem($item)))
            {
                // No es un producto configurable, salimos
                return [];
            }
            /** @noinspection PhpParamsInspection */
            $childSizeDescription = $childProduct->getProduct()->getAttributeText('size');
            $availableSizes = Mage::helper('rmasystem')->getAvailableSizes($item->getProduct());
            return array_filter($availableSizes, function(Webkul_RmaSystem_Model_Sizeinfo $size) use($childSizeDescription)
            {
                return $size->getDescription() !== $childSizeDescription;
            });
        }
        /**
         * Obtiene el item de producto simple vinculado a un producto configurable del pedido
         * @param Mage_Sales_Model_Order_Item $parentItem
         * @return Mage_Sales_Model_Order_Item|null
         */
        public function getLinkedSimpleItem(Mage_Sales_Model_Order_Item $parentItem)
        {
            /** @var Mage_Sales_Model_Order_Item $item */
            foreach($parentItem->getOrder()->getAllItems() as $item)
            {
                if($item->getParentItemId() == $parentItem->getId())
                {
                    return $item;
                }
            }
            return null;
        }
        /**
         * @param int $productId
         * @param int $quantity
         */
        public function restoreStock($productId, $quantity)
        {
            $this->log('Devolviendo %s unidades al stock del producto %s...', [$quantity, $productId]);
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            $stockItem->setManageStock(1);
            $stockItem->setQty($stockItem->getQty() + $quantity);
            $stockItem->save();
        }
        /**
         * @param int $orderId
         * @return bool
         */
        public function orderQualifiesForRma($orderId)
        {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($orderId);
            /** @var Mage_Sales_Model_Order_Item $item */
            foreach($order->getAllVisibleItems() as $item)
            {
                if($this->orderItemQuailifiesForRma($item))
                {
                    return true;
                }
            }
            return false;
        }
        /**
         * @param Mage_Sales_Model_Order_Item $item
         * @return bool
         */
        public function orderItemQuailifiesForRma(Mage_Sales_Model_Order_Item $item)
        {
            return $this->getQtyAvailableForRefund($item) > 0;
        }
        /**
         * Devuelve el número de unidades de una línea de pedido de
         * las que se puede solicitar devolución
         * @param Mage_Sales_Model_Order_Item $item
         * @return int
         */
        public function getQtyAvailableForRefund(Mage_Sales_Model_Order_Item $item)
        {
            // La cantidad permitida para devolver la tomamos a partir de la cantidad
            // original pedido, a la que le restamos:
            // - Las unidades que ya hayan sido devueltas
            // - Las unidades que se encuentren ya involucradas en un proceso de
            //   devolución abierto
            $qty = $item->getQtyOrdered() - $item->getQtyRefunded();
            /** @var Webkul_RmaSystem_Model_Mysql4_Items_Collection $rmaItems */
            $rmaItems = Mage::getModel('rmasystem/items')
                ->getCollection()
                ->join(
                    ['rma' => 'rmasystem/rma'],
                    'rma_id = rma.id',
                    ['status' => 'status'])
                ->addFieldToFilter('item_id', $item->getId())
                ->addFieldToFilter('status', [ 'nin' => [
                    Webkul_RmaSystem_Model_Constants::StatusAccepted,
                    Webkul_RmaSystem_Model_Constants::StatusCancelled,
                    Webkul_RmaSystem_Model_Constants::StatusDenied
                ]]);
            foreach($rmaItems as $rmaItem)
            {
                $qty -= $rmaItem->getQty();
            }
            return (int) $qty;
        }
        /**
         * @param int $customerId
         * @param int $rmaId
         * @return string
         */
        public function getLabelUrl($customerId, $rmaId)
        {
            return sprintf('/media/rmalabels/%s.pdf', $this->getLabelName($customerId, $rmaId));
        }
        /**
         * @param int $customerId
         * @param int $rmaId
         * @return string
         */
        public function getLabelName($customerId, $rmaId)
        {
            $salt = 'ZmVlYTJlNWRmODFlY2ZlYjFiODk2OTZi';
            return sha1($salt . $customerId. $rmaId);
        }
	}