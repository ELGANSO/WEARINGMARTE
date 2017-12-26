<?php

	require_once "Mage/Customer/controllers/AccountController.php";
	require_once Mage::getBaseDir("lib")."/rmasystem/Barcode39.php";
	class Webkul_RmaSystem_IndexController extends Mage_Customer_AccountController	{

	    const ResolutionTypeRefund = 0;
        const ResolutionTypeExchange = 1;

		public function indexAction() {
			$this->loadLayout(array("default","rmasystem_account"));
			$this->getLayout()->getBlock("head")->setTitle($this->__("RMA System"));
			$this->renderLayout();
		}

		public function newAction(){
			$this->loadLayout(array("default","rmasystem_index_new"));
			$this->getLayout()->getBlock("head")->setTitle($this->__("File Your Returns"));
			$this->renderLayout();
		}

		public function setfilterSessionAction(){
			$data = $this->getRequest()->getPost();
			Mage::getSingleton("customer/session")->setFilterData($data);
		}

		public function setsortingSessionAction(){
			$data = $this->getRequest()->getPost();
			Mage::getSingleton("customer/session")->setSortingData($data);
		}

		public function setrmasortingSessionAction(){
			$data = $this->getRequest()->getPost();
			Mage::getSingleton("customer/session")->setRmaSortingData($data);
		}

		public function setrmafilterSessionAction(){
			$data = $this->getRequest()->getPost();
			Mage::getSingleton("customer/session")->setRmaFilterData($data);
		}

		public function getorderDetailsAction(){
			$data = $this->getRequest()->getParams();
			$order_id=$data['order_id']; 
			$order_details = array();
			
			$all_items = Mage::getModel("sales/order")->load($order_id)->getAllVisibleItems();

			foreach($all_items as $item){
				$url = Mage::getModel("catalog/product")->getCollection()->addFieldToFilter("entity_id",$item->getProductId())->getFirstItem()->getProductUrl();

				$qty_order=$item->getQtyOrdered();
	            $qty_ship=$item->getQtyShipped();
				if($qty_order==$qty_ship){
	            	array_push($order_details,array("url" => $url,"name" => $item->getName(),"sku" => $item->getSku(),"qty" => intval($item->getQtyOrdered()),"itemid" => $item->getItemId(),"product_id"=>$item->getProductId(),"price"=>$item->getPrice()));
	             }
			}
	    		$this->getResponse()->setHeader("Content-type","application/json");
				$this->getResponse()->setBody(Mage::helper("core")->jsonEncode(array_reverse($order_details)));
		}

        public function getitemDetailsAction()
        {
            $data = $this->getRequest()->getParams();
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel("sales/order")->load($data["order_id"]);
            $all_items = $order->getAllVisibleItems();

            // Obtenemos primero los datos del pedido
            $address = $order->getShippingAddress();

            $order_details = [
                'has_shipped' => $order->getShipmentsCollection()->count() > 0,
                'address' => $address->getStreet2(),
                'number' => $address->getStreet3(),
                'postcode' => $address->getPostcode(),
                'city' => $address->getCity(),
                'phone' => $address->getTelephone(),
                'items' => []
            ];

            $helper = Mage::helper('rmasystem');
            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($all_items as $item)
            {
             	if(!$helper->orderItemQuailifiesForRma($item))
                {
                    continue;
                }

                $itemDetails = null;
                $qty_order = $item->getQtyOrdered();
                $qty_invoiced = $item->getQtyInvoiced();
                $tangibleProduct = $item->getProductType() !== 'downloadable' && $item->getProductType() !== 'virtual';
                $qtyMismatch = $qty_order !== $qty_invoiced;

                if (!$tangibleProduct && !$qtyMismatch)
                {
                    continue;
                }

                if($item->getQtyCanceled() + $item->getQtyRefunded() >= $item->getQtyOrdered())
                {
                    continue;
                }

                $url = Mage::getModel("catalog/product")
                    ->getCollection()
                    ->addFieldToFilter("entity_id", $item->getProductId())
                    ->getFirstItem()
                    ->getProductUrl();

                $itemDetails = [
                    "url" => $url,
                    "name" => $item->getName(),
                    "sku" => $item->getSku(),
                    "qty" => intval($item->getQtyOrdered()),
                    "itemid" => $item->getItemId(),
                    "product_id" => $item->getProductId(),
                    "price" => Mage::helper('core')->currency($item->getPriceInclTax(), true, false)
                ];

                $itemDetails['available_sizes'] = [];

                // Si el producto es de tipo configurable obtenemos sus tallas disponibles
                $product = $item->getProduct();
                $helper = Mage::helper('rmasystem');
                if($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                {
                    // Devolvemos la talla seleccionada originalmente
                    $childItem = $helper->getLinkedSimpleItem($item);
                    $itemDetails['size'] = $childItem->getProduct()->getAttributeText('size');

                    // Y el resto de tallas disponibles
                    foreach($helper->getOtherAvailableSizes($item) as $availableSize)
                    {
                        $itemDetails['available_sizes'][] = [
                            'product_id' => $availableSize->getProductId(),
                            'description' => $availableSize->getDescription()
                        ];
                    }
                }

                $order_details['items'][] = $itemDetails;
            }
            $this->getResponse()->setHeader("Content-type","application/json");
            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode(array_reverse($order_details)));
        }

        public function savermaAction()
        {
            $post = $this->getRequest()->getPost();
            $rmaHelper = Mage::helper('rmasystem/rma');
            if ($rma = $rmaHelper->saveRma($post, $_FILES, $error, null, $displayLabel))
            {
                if ($error == true)
                {
                    Mage::getSingleton('core/session')->addNotice($this->__('All files may not be uploaded'));
                }
                Mage::getSingleton('core/session')->addSuccess($this->__('RMA Saved Successfully'));

                if($displayLabel)
                {
                    $this->_redirect('*/index/label', [ 'rma' => $rma->getId()]);
                }
                else
                {
                    $this->_redirect('*/');
                }
            }
            else
            {
                Mage::getSingleton('core/session')->addError($this->__('Unable to save'));
                $this->_redirect('*/index/new');
            }
        }

        public function labelAction()
        {
            $this->loadLayout();
            $this->getLayout()->getBlock('rma_label')->setRmaId($this->getRequest()->get('rma'));
            $this->renderLayout();
        }

        public function viewAction(){
        	$id = $this->getRequest()->getParam("id");
        	$rma = Mage::getModel("rmasystem/rma")->load($id);
        	if($rma->getCustomerId() == Mage::getSingleton("customer/session")->getId()){
        		$this->loadLayout(array("default","rmasystem_view"));
				$this->getLayout()->getBlock("head")->setTitle($this->__("RMA Details"));
				$this->renderLayout();
        	}
        	else{
        		Mage::getSingleton("core/session")->addError($this->__("Sorry You Are Not Authorised to view this RMA request"));
		    	$this->_redirect("*/*/");
        	}
        }

        protected function printAction(){
        	$id = $this->getRequest()->getParam("id");
        	$rma = Mage::getModel("rmasystem/rma")->load($id);
        	if($rma->getCustomerId() == Mage::getSingleton("customer/session")->getId()){
        		$this->loadLayout(array("default","rmasystem_print"));
				$this->getLayout()->getBlock("head")->setTitle($this->__("RMA Details"));
				$this->renderLayout();
        	}
        	else{
        		Mage::getSingleton("core/session")->addError($this->__("Sorry You Are Not Authorised to print this RMA request"));
		    	$this->_redirect("*/*/");
        	}
        }

        protected function printlabelAction(){
        	$id = $this->getRequest()->getParam("id");  
        	$rma = Mage::getModel("rmasystem/rma")->load($id);
        	if($rma->getCustomerId() == Mage::getSingleton("customer/session")->getId() && $rma->getShippingLabel() > 0){
        		$this->loadLayout(array("default","rmasystem_printlabel"));
				$this->getLayout()->getBlock("head")->setTitle($this->__("Pre Shipping Label"));
				$this->renderLayout();
        	}
        	else{
        		Mage::getSingleton("core/session")->addError($this->__("Shipping Label Not Available"));
		    	$this->_redirect("*/*/");
        	}
	    }

        protected function cancelAction(){
        	$id = $this->getRequest()->getParam("id");
            /** @var Webkul_RmaSystem_Model_Rma $rma */
        	$rma = Mage::getModel("rmasystem/rma")->load($id);
        	if($rma->getCustomerId() == Mage::getSingleton("customer/session")->getId()){
        		$rma->setStatus(Webkul_RmaSystem_Model_Constants::StatusCancelled)->save();
        		Mage::getModel("rmasystem/mails")->CancelRma($id,$rma->getGroup());
        		Mage::getSingleton("core/session")->addSuccess($this->__("RMA with id ").$id.$this->__(" has been cancelled successfully"));
		    	$this->_redirect("*/");
        	}
        	else{
        		Mage::getSingleton("core/session")->addError($this->__("Sorry You Are Not Authorised to cancel this RMA request"));
		    	$this->_redirect("*/*/");
        	}
        }

        protected function updatermaAction()
        {
        	$post = $this->getRequest()->getPost();
            if(!$post)
            {
                Mage::getSingleton('core/session')->addError($this->__('Unable to save'));
                $this->_redirect('*/index/view',array('id',$post['rma_id']));
            }

            /** @var Webkul_RmaSystem_Model_Rma $rma */
            $rma = Mage::getModel('rmasystem/rma')->load($post['rma_id']);

            if($post['message'] != '')
            {
                /** @var Webkul_RmaSystem_Model_Conversation $conversation */
                $conversation = Mage::getModel('rmasystem/conversation');
                $conversation->setRmaId($post['rma_id'])
                    ->setMessage($post['message'])
                    ->setCreatedAt(time())
                    ->setSender(Mage::getSingleton('customer/session')->getId())
                    ->save();
            }

            if(isset($post['solved']))
            {
                $rma->setStatus(Webkul_RmaSystem_Model_Constants::StatusCancelled);
                $rma->save();
                Mage::getModel('rmasystem/mails')->RMAUpdate($post, true, false, $rma->getGroup());
            }
            else
            {
                Mage::getModel('rmasystem/mails')->NewMessage($post,$rma->getGroup(),'front');
            }

            Mage::getSingleton('core/session')->addSuccess($this->__('RMA Successfully Updated'));
            $this->_redirect('*/index/view/', array('id' => $post['rma_id']));
        }
	}