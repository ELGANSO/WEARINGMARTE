<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Controller
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
    /**
     * Check order view availability
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }

    /**
     * Init layout, messages and set active block for customer
     *
     * @return null
     */
    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
        }
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }

    /**
     * Order view page
     */
    public function viewAction()
    {
        $this->_viewAction();
    }
   public function ajaxAction()
    {
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        if($this->_loadValidOrder($order_id))
        {
        	try{
        		$order = Mage::registry('current_order');
        		$data = $order->getData();

        		$html .= '<div class="page-title">
					    <p>'.$this->__("El pedido #%s se realizó el %s y esta actualmente ", $order->getRealOrderId(), $this->getFormatedDate($order)).'
					    	<span>'.$order->getStatusLabel().'</span>.
					    </p>
					</div>';
				$html .= '<h3 class="order-details">Detalles del pedido</h3>';
				//Items del pedido
        		foreach ($order->getAllItems() as $item ) {
        			if(!$item->getParentItemId())
        				$html .= $this->getItemHtml($item);
        		}
        		//Totales
		        $shipping = floatval($order->getBaseShippingInclTax());
				$surcharge = floatval($order->getBaseFoomanSurchargeAmount());
				$gastos = number_format($shipping + $surcharge,2);
				$pago = Mage::getStoreConfig("payment/".$order->getPayment()->getMethod()."/title");
        		$html .= '<div class="row" style=" border-top: 1px solid #d2cece;">
							<div class="col-lg-4 col-md-4 col-xs-12"></div>
							<div class="col-lg-8 col-md-8 col-xs-12 subtotals">
								<li>'.$this->__("Subtotal ").'<span>'.number_format($order->getSubtotal(),2).'€</span></li>
								<li>'.$this->__("Envío").'<span>'.$gastos.'€</span></li>
								<li>'.$this->__("Método de pago").'<span>'.$pago.'</span></li>
							</div>
							<div class="col-lg-4 col-md-4 col-xs-12"></div>
							<div class="col-lg-8 col-md-8 col-xs-12 total">
								<li>'.$this->__("Total").'<span>'.number_format($order->getGrandTotal(),2).'€</span></li>
								<li><span>'.$this->__('(21% IVA incluido)').'</span></li>
							</div>
        		';
        		//Datos del cliente
        		$html .= '<div class="row">
							<h3 class="order-details">Detalles del cliente</h3>
        					<div class="col-lg-8 col-md-8 col-xs-12 client-details">
								<li>'.$this->__("Email").'  <span>'.$order->getCustomerEmail().'</span> </li>
								<li>'.$this->__("Teléfono").' <span>'.$order->getShippingAddress()->getTelephone().'</span></li>
							</div>
							<div class="col-lg-4 col-md-4 col-xs-12"></div>
						</div>
        		';
        		//Envio y facturacion
        	$html .='
				<div class="row">
				<div class="col-lg-6 col-md-6 col-xs-12 order-info-box">
				        <div class="">
				            <div class="box-title">
				                <h2>'.$this->__('Detalles de facturacion').'</h2>
				            </div>
				            <div class="box-content">
				                <address>'.$order->getBillingAddress()->format('html').'</address>
				            </div>
				        </div>
				</div>

				<div class="col-lg-6 col-md-6 col-xs-12 order-info-box">
				        <div class="">
				            <div class="box-title">
				                <h2>'.$this->__('Detalles de envío').'</h2>
				            </div>
				            <div class="box-content">
				                <address>'.$order->getShippingAddress()->format("html").'</address>
				            </div>
				        </div>
				</div>

		</div>';
        		echo $html;

        	}catch(Exception $e){
        		echo $e;
        	}
			
        }

        //echo json_encode( Mage::register('current_order'));
    }
    protected function getItemHtml($item){

    	return '<div class="row item">
        	<div class="col-lg-4 col-md-4 col-xs-3">
            	<img src="'.Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail').'" />
            </div>            
            <div class="col-lg-3 col-md-3 col-xs-3">
                <input type="hidden" value="1" name="wishlist[<?php echo $item->getItemId() ?>]" />
                <a href="'.Mage::getUrl('catalog/product/view/id/'.$item->getProduct()->getId()) .'">'.$item->getProduct()->getName().'</a>
            </div>
            <div class="col-lg-2 col-md-3 col-xs-3"><span>'.number_format($item->getPrice(),2) .'€</span></div>
            <div class="col-lg-3 col-md-3 col-xs-3">
            	<a href="'.$item->getProduct()->getProductUrl().'" class="ver-producto"> Ver Producto </a>
            </div>
        </div>';
    }
    protected function getFormatedDate($_order)
    {
    	    //Formatei fecha para mostrarla según diseño
    $date = explode("/",Mage::helper('core')->formatDate($_order->getCreatedAtStoreDate()));
	$month = $this->__(date('F',strtotime(Mage::helper('core')->formatDate($_order->getCreatedAtStoreDate()))));
	$de = $this->__("de");
	return $date[0]." ".$de." ".$month.", ".$date[2];
    }
    /**
     * Invoice page
     */
    public function invoiceAction()
    {
        $this->_viewAction();
    }

    /**
     * Shipment page
     */
    public function shipmentAction()
    {
        $this->_viewAction();
    }

    /**
     * Creditmemo page
     */
    public function creditmemoAction()
    {
        $this->_viewAction();
    }

    /**
     * Action for reorder
     */
    public function reorderAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }
        $order = Mage::registry('current_order');

        $cart = Mage::getSingleton('checkout/cart');
        $cartTruncated = false;
        /* @var $cart Mage_Checkout_Model_Cart */

        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Mage_Core_Exception $e){
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
                $this->_redirect('*/*/history');
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e,
                    Mage::helper('checkout')->__('Cannot add the item to shopping cart.')
                );
                $this->_redirect('checkout/cart');
            }
        }

        $cart->save();
        $this->_redirect('checkout/cart');
    }

    /**
     * Print Order Action
     */
    public function printAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }

    /**
     * Print Invoice Action
     */
    public function printInvoiceAction()
    {
        $invoiceId = (int) $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $order = $invoice->getOrder();
        } else {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
        }

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            if (isset($invoice)) {
                Mage::register('current_invoice', $invoice);
            }
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }

    /**
     * Print Shipment Action
     */
    public function printShipmentAction()
    {
        $shipmentId = (int) $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            $order = $shipment->getOrder();
        } else {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
        }
        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            if (isset($shipment)) {
                Mage::register('current_shipment', $shipment);
            }
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }

    /**
     * Print Creditmemo Action
     */
    public function printCreditmemoAction()
    {
        $creditmemoId = (int) $this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            $order = $creditmemo->getOrder();
        } else {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
        }

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            if (isset($creditmemo)) {
                Mage::register('current_creditmemo', $creditmemo);
            }
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }
}
