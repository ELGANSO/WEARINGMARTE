<?php

	class Webkul_RmaSystem_Model_Mails extends Mage_Core_Model_Abstract    {

		public function NewRma($post,$last_rma_id,$group)  {
			$_helper = Mage::helper("rmasystem");
			$rma = Mage::getModel("rmasystem/rma")->load($last_rma_id);
			$admin_name = Mage::getStoreConfig("rmasystem/rmasystem/adminname",Mage::app()->getStore());
			$admin_email = Mage::getStoreConfig("rmasystem/rmasystem/adminemail",Mage::app()->getStore());
			$customer_name = "";$customer_email = "";
			if($group == "customer"){
				$customer = Mage::getSingleton("customer/session")->getCustomer();
				$customer_name = $customer->getName();
				$customer_email = $customer->getEmail();
			}
			else{
				$order = Mage::getModel("sales/order")->load($rma->getOrderId());
				$customer_name = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
				$customer_email = $order->getCustomerEmail();
			}
			$template_variable = array();
			$template_variable["rma_id"] = $rma->getIncrementId()."-".$last_rma_id;
			$template_variable["order_id"] = $rma->getIncrementId();
			//Package Condition
			if($rma->getPackageCondition() == 0)
			    $template_variable["package_condition"] = $_helper->__("Open");
            else
            	$template_variable["package_condition"] = $_helper->__("Packed");
            //Resolution Type
			if($rma->getResolutionType() == 0)
			    $template_variable["resolution_type"] = $_helper->__("Refund");
            else
			    $template_variable["resolution_type"] = $_helper->__("Exchange");
			$template_variable["additional_info"] = nl2br(strip_tags($rma->getAdditionalInfo()));
			//delivery consignment number check
			if($rma->getCustomerDeliveryStatus() == 1){
				$delivery_status = "<tbody><tr><th colspan='2' align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;''>".$_helper->__('Customer Consignment Number')." :</th></tr><tr><td colspan='2' valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma->getCustomerConsignmentNo()."</td></tr></tbody>";
				$template_variable["delivery_status"] = $delivery_status;
			}
			//RMA items listing
			$rma_items = Mage::getModel("rmasystem/items")->getCollection()->addFieldToFilter("rma_id",$last_rma_id);
			$count = 1;$rma_item_html = "";
			foreach($rma_items as $item) {
				$mage_item = Mage::getModel("sales/order_item")->load($item->getItemId());
				$product = Mage::getModel("catalog/product")->load($mage_item->getProductId());
				$rma_item_html .= "<tbody ";
				if($count % 2 != 0)
					$rma_item_html .= "bgcolor='#F6F6F6'";
				$rma_item_html .= "><tr><td align='left' valign='top' style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'><strong style='font-size:11px'>".$product->getName()."</strong></td><td align='left' valign='top' style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>".$product->getSku()."</td><td align='center' valign='top' style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'>".$item->getQty()."</td><td align='right' valign='top' style='font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc'><span>".Mage::getModel('rmasystem/reason')->load($item->getReasonId())->getReason()."</span></td></tr></tbody>";
				$count++;
			}
			$template_variable["items"] = $rma_item_html;
			////////////////////////////////////////////////Mail To Customer////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $customer_name;					 	 						/**/
			/**/$template_variable["title"] = $_helper->__("Thanks for your RMA request, will contact you soon.");	/**/
			/**/$template_variable["rma_link_label"] = "Click here to view RMA :";
			/**/$template_variable["rma_link"] = Mage::getUrl('rmasystem/index/view',array("id" => $last_rma_id));
            $email_template = Mage::getModel("core/email_template")->loadDefault("new_rma");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 						/**/
			/**/$email_template->setSenderName($admin_name);							  	 						/**/
			/**/$email_template->setSenderEmail($admin_email);							 	 						/**/
			/**/$email_template->send($customer_email,$customer_name,$template_variable);	 						/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////Mail To Admin///////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $admin_name;					 	 	 	   					/**/
			/**/$template_variable["title"] = $_helper->__("You got new RMA request. Please Check.");				/**/
			/**/$template_variable["rma_link_label"] = "Click here to login :";
			/**/$template_variable["rma_link"] = Mage::helper("adminhtml")->getUrl("adminhtml/dashboard/index");				/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("new_rma");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 	   					/**/
			/**/$email_template->setSenderName($customer_name);							  	 	   					/**/
			/**/$email_template->setSenderEmail($customer_email);							 	   					/**/
			/**/$email_template->send($admin_email,$admin_name,$template_variable);	 		 	   					/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}

		public function CancelRma($id,$group){
			$rma = Mage::getModel("rmasystem/rma")->load($id);
			$admin_name = Mage::getStoreConfig("rmasystem/rmasystem/adminname",Mage::app()->getStore());
			$admin_email = Mage::getStoreConfig("rmasystem/rmasystem/adminemail",Mage::app()->getStore());
			$customer_name = "";$customer_email = "";
			if($group == "customer"){
				$customer = Mage::getSingleton("customer/session")->getCustomer();
				$customer_name = $customer->getName();
				$customer_email = $customer->getEmail();
			}
			else{
				$order = Mage::getModel("sales/order")->load($rma->getOrderId());
				$customer_name = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
				$customer_email = $order->getCustomerEmail();
			}
			$template_variable = array();
			$_helper = Mage::helper("rmasystem");
			$template_variable["rma_id"] = $rma->getIncrementId()."-".$id;
			$template_variable["order_id"] = $rma->getIncrementId();
			$template_variable["status"] = $_helper->__("Cancelled");
			//////////////////////////////////////////////////Mail To Customer//////////////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $customer_name;					 	 									/**/
			/**/$template_variable["title"] = $_helper->__("You have just cancelled your RMA request, Details are as follows.");/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("cancel_rma");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 									/**/
			/**/$email_template->setSenderName($admin_name);							  	 									/**/
			/**/$email_template->setSenderEmail($admin_email);							 	 									/**/
			/**/$email_template->send($customer_email,$customer_name,$template_variable);	 									/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////////Mail To Admin///////////////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $admin_name;					 	 	 	   								/**/
			/**/$template_variable["title"] = $_helper->__("One RMA request, Details are as follows");							/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("cancel_rma");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 	   								/**/
			/**/$email_template->setSenderName($customer_name);							  	 	   								/**/
			/**/$email_template->setSenderEmail($customer_email);							 	   								/**/
			/**/$email_template->send($admin_email,$admin_name,$template_variable);	 		 	   								/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}

		public function NewMessage($post,$group,$area){
			$rma = Mage::getModel("rmasystem/rma")->load($post["rma_id"]);
			$admin_name = Mage::getStoreConfig("rmasystem/rmasystem/adminname",Mage::app()->getStore());
			$admin_email = Mage::getStoreConfig("rmasystem/rmasystem/adminemail",Mage::app()->getStore());
			$order = Mage::getModel("sales/order")->load($rma->getOrderId());
			$customer_name = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
			$customer_email = $order->getCustomerEmail();
			$template_variable = array();
			$_helper = Mage::helper("rmasystem");
			$template_variable["rma_id"] = $rma->getIncrementId()."-".$post["rma_id"];
			$template_variable["order_id"] = $rma->getIncrementId();
			$template_variable["message"] = nl2br(strip_tags($post["message"]));
			////////////////////////////////////////////////Mail To Customer////////////////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $customer_name;					 	 									/**/
			/**/if($area == "front")																							/**/
			/**/	$template_variable["title"] = $_helper->__("Your Message has been successfully saved for following RMA.");	/**/
			/**/else 																											/**/
			/**/	$template_variable["title"] = $_helper->__("New Message has been appended to following RMA.");				/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("new_message");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 									/**/
			/**/$email_template->setSenderName($admin_name);							  	 									/**/
			/**/$email_template->setSenderEmail($admin_email);							 	 									/**/
			/**/$email_template->send($customer_email,$customer_name,$template_variable);	 									/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////Mail To Admin//////////////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $admin_name;					 	 	 	   								/**/
			/**/if($area == "admin")																							/**/
			/**/	$template_variable["title"] = $_helper->__("Your Message has been successfully saved for following RMA.");	/**/
			/**/else 																											/**/
			/**/	$template_variable["title"] = $_helper->__("New Message has been appended to following RMA.");				/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("new_message");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 	   								/**/
			/**/$email_template->setSenderName($customer_name);							  	 	   								/**/
			/**/$email_template->setSenderEmail($customer_email);							 	   								/**/
			/**/$email_template->send($admin_email,$admin_name,$template_variable);	 		 	   								/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}

		public function RMAUpdate($post,$status_flag,$delivery_flag,$group){
		    /** @var Webkul_RmaSystem_Model_Rma $rma */
			$rma = Mage::getModel("rmasystem/rma")->load($post["rma_id"]);
			$admin_name = Mage::getStoreConfig("rmasystem/rmasystem/adminname",Mage::app()->getStore());
			$admin_email = Mage::getStoreConfig("rmasystem/rmasystem/adminemail",Mage::app()->getStore());
			$customer_name = "";$customer_email = "";$_helper = Mage::helper("rmasystem");
			if($group == "customer"){
				$customer = Mage::getSingleton("customer/session")->getCustomer();
				$customer_name = $customer->getName();
				$customer_email = $customer->getEmail();
			}
			else{
				$order = Mage::getModel("sales/order")->load($rma->getOrderId());
				$customer_name = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
				$customer_email = $order->getCustomerEmail();
			}
			$status_data = "";
			if($status_flag == true && $delivery_flag == true){
			    $rma_status = $rma->getStatusText();
				$status_data .= "<tbody><tr><th align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Customer Consignment Number')." :</th><th align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Status')." :</th></tr><tr><td valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma->getCustomerConsignmentNo()."</td><td valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma_status."</td></tr></tbody>";
			}
			else
			if($status_flag == true){
			    $rma_status = $rma->getStatusText();
				$status_data .= "<tbody><tr><th colspan='2' align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Status')." :</th></tr><tr><td colspan='2' valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma_status."</td></tr></tbody>";
			}
			else
			if($delivery_flag == true){
				$status_data .= "<tbody><tr><th colspan='2' align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Customer Consignment Number')." :</th></tr><tr><td colspan='2' valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma->getCustomerConsignmentNo()."</td></tr></tbody>";
			}
			$template_variable = array();
			$template_variable["rma_id"] = $rma->getIncrementId()."-".$post["rma_id"];
			$template_variable["order_id"] = $rma->getIncrementId();
			$template_variable["message"] = nl2br(strip_tags($post["message"]));
			$template_variable["status_data"] = $status_data;
			////////////////////////////////////////////Mail To Customer////////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $customer_name;					 	 						/**/
			/**/$template_variable["title"] = $_helper->__("Your RMA Updated successfully details are as follows.");/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("rma_update");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 						/**/
			/**/$email_template->setSenderName($admin_name);							  	 						/**/
			/**/$email_template->setSenderEmail($admin_email);							 	 						/**/
			/**/$email_template->send($customer_email,$customer_name,$template_variable);	 						/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////Mail To Admin//////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $admin_name;					 	 	 	   					/**/
			/**/$template_variable["title"] = $_helper->__("RMA Updated details are as follows."); 					/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("rma_update");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 	   					/**/
			/**/$email_template->setSenderName($customer_name);							  	 	   					/**/
			/**/$email_template->setSenderEmail($customer_email);							 	   					/**/
			/**/$email_template->send($admin_email,$admin_name,$template_variable);	 		 	   					/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}

		public function RMAUpdateAdmin($post,$status_flag,$delivery_flag){
		    /** @var Webkul_RmaSystem_Model_Rma $rma */
			$rma = Mage::getModel("rmasystem/rma")->load($post["rma_id"]);
			$admin_name = Mage::getStoreConfig("rmasystem/rmasystem/adminname",Mage::app()->getStore());
			$admin_email = Mage::getStoreConfig("rmasystem/rmasystem/adminemail",Mage::app()->getStore());
			$customer_name = "";$customer_email = "";$_helper = Mage::helper("rmasystem");
			$order = Mage::getModel("sales/order")->load($rma->getOrderId());
			$customer_name = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
			$customer_email = $order->getCustomerEmail();
			$status_data = "";
			if($status_flag == true && $delivery_flag == true){
			    $rma_status = $rma->getStatusText();
				$status_data .= "<tbody><tr><th align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Admin Consignment Number')." :</th><th align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Status')." :</th></tr><tr><td valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma->getAdminConsignmentNo()."</td><td valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma_status."</td></tr></tbody>";
			}
			else
			if($status_flag == true){
                $rma_status = Mage::getModel('rmasystem/rma')->getStatusOptions()[$rma['status']];
				$status_data .= "<tbody><tr><th colspan='2' align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Status')." :</th></tr><tr><td colspan='2' valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma_status."</td></tr></tbody>";
			}
			else
			if($delivery_flag == true){
				$status_data .= "<tbody><tr><th colspan='2' align='left' bgcolor='#EAEAEA' style='font-size:13px;padding:5px 9px 6px 9px;line-height:1em;'>".$_helper->__('Admin Consignment Number')." :</th></tr><tr><td colspan='2' valign='top' style='font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;'>".$rma->getAdminConsignmentNo()."</td></tr></tbody>";
			}
			$template_variable = array();
			$template_variable["rma_id"] = $rma->getIncrementId()."-".$post["rma_id"];
			$template_variable["order_id"] = $rma->getIncrementId();
			$template_variable["message"] = nl2br(strip_tags($post["message"]));
			$template_variable["status_data"] = $status_data;
			////////////////////////////////////////////////////////Mail To Customer////////////////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $customer_name;					 	 											/**/
			/**/$template_variable["title"] = $_helper->__("Your RMA Request has been Updated. Please check details are as follows.");	/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("rma_update");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 											/**/
			/**/$email_template->setSenderName($admin_name);							  	 											/**/
			/**/$email_template->setSenderEmail($admin_email);							 	 											/**/
			/**/$email_template->send($customer_email,$customer_name,$template_variable);	 											/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////Mail To Admin//////////////////////////////////////////////////////////
			/**/$template_variable["receiver_name"] = $admin_name;					 	 	 	   										/**/
			/**/$template_variable["title"] = $_helper->__("RMA Updated successfully details are as follows."); 						/**/
            $email_template = Mage::getModel("core/email_template")->loadDefault("rma_update");
			/**/$email_template->getProcessedTemplate($template_variable);				 	 	   										/**/
			/**/$email_template->setSenderName($admin_name);							  	 	   										/**/
			/**/$email_template->setSenderEmail($admin_email);							 	   										/**/
			/**/$email_template->send($admin_email,$admin_name,$template_variable);	 		 	   										/**/
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}

	}