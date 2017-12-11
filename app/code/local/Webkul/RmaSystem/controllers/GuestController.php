<?php   

    require_once Mage::getBaseDir("lib")."/rmasystem/Barcode39.php";
    class Webkul_RmaSystem_GuestController  extends Mage_Core_Controller_Front_Action {

        public function indexAction() {
            $data = Mage::getSingleton("core/session")->getGuestData();
           // if(Mage::getSingleton("customer/session")->isLoggedIn()){
                //Mage::app()->getResponse()->setRedirect(Mage::getUrl("rmasystem/index/index"));
           // }
           // else
            if(isset($data))
                $this->_redirect("*/guest/rmalist");
            else {
                $this->loadLayout(array("default","rmasystem_guest_index"));
                $this->getLayout()->getBlock("head")->setTitle($this->__("Rma guest Login"));
                $this->renderLayout();
            }
        }

        public function setfilterSessionAction(){
            $data = $this->getRequest()->getPost();
            Mage::getSingleton("core/session")->setGuestFilterData($data);
        }

        public function setsortingSessionAction(){
            $data = $this->getRequest()->getPost();
            Mage::getSingleton("core/session")->setGuestSortingData($data);
        }

        public function setrmasortingSessionAction(){
            $data = $this->getRequest()->getPost();
            Mage::getSingleton("core/session")->setGuestRmaSortingData($data);
        }

        public function setrmafilterSessionAction(){
            $data = $this->getRequest()->getPost();
            Mage::getSingleton("core/session")->setGuestRmaFilterData($data);
        }

        public function loginpostAction() {
            $post = $this->getrequest()->getPost();
            $order = Mage::getModel("sales/order")->loadByIncrementId($post["order_id"]);
            $email = $order->getCustomerEmail();
            if($email == $post["email"] && $order->getCustomerGroupId() == 1) {
                Mage::getSingleton("core/session")->addSuccess($this->__("Login Successful"));
                Mage::getSingleton("core/session")->setGuestData($post);
                $this->_redirect("*/guest/rmalist");
            }
            else {
                Mage::getSingleton("core/session")->addError($this->__("Invalid Details")." - ".$post["email"]."- ".$order->getCustomerGroupId());
                $this->_redirect("*/*/");
            }
        }

        public function rmalistAction() {
            $data = Mage::getSingleton("core/session")->getGuestData();
            if(isset($data)){
                $this->loadLayout(array("default","rmasystem_guest_rmalist"));
                $this->getLayout()->getBlock("head")->setTitle($this->__("RMA List"));
                $this->renderLayout();
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Please Login"));
                $this->_redirect("*/*/");
            }
        }

        public function newAction() {
            $data = Mage::getSingleton("core/session")->getGuestData();
            if(isset($data)){
                $this->loadLayout(array("default","rmasystem_guest_new"));
                $this->getLayout()->getBlock("head")->setTitle($this->__("File Your Returns"));
                $this->renderLayout();
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Please Login"));
                $this->_redirect("*/*/");
            }
        }

        public function getorderDetailsAction(){
            $data = $this->getRequest()->getPost();
            $order_id=$data['id']; 
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

    public function getitemDetailsAction(){
        $data = $this->getRequest()->getPost();
        $all_items = Mage::getModel("sales/order")->load($data["id"])->getAllVisibleItems();
        $order_details = array();
        foreach($all_items as $item){
            if($item->getProductType()!=='downloadable'){
            $url = Mage::getModel("catalog/product")->getCollection()->addFieldToFilter("entity_id",$item->getProductId())->addFieldToFilter('type_id',array('neq'=>'downloadable'))->getFirstItem()->getProductUrl();
            array_push($order_details,array("url" => $url,"name" => $item->getName(),"sku" => $item->getSku(),"qty" => intval($item->getQtyOrdered()),"itemid" => $item->getItemId(),"product_id"=>$item->getProductId(),"price"=>$item->getPrice()));
            }
        }
        $this->getResponse()->setHeader("Content-type","application/json");
        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode(array_reverse($order_details)));
    }

        public function savermaAction(){
            $post = $this->getRequest()->getPost();
            $data = Mage::getSingleton("core/session")->getGuestData();
            if(isset($data)){
                $error = false;
                if($post){
                    $file = new Varien_Io_File();
                    $rma = Mage::getModel("rmasystem/rma")
                            ->setOrderId($post["order_id"])
                            ->setGroup("guest")
                            ->setIncrementId($post["increment_id"])
                            ->setAdditionalInfo($post["additional_info"])
                            ->setResolutionType($post["resolution_type"])
                            ->setPackageCondition($post["package_condition"])
                            ->setCustomerDeliveryStatus($post["customer_delivery_status"])
                            ->setCustomerConsignmentNo($post["customer_consignment_no"])
                            ->setGuestEmail($data["email"])
                            ->setStatus(1)
                            ->setCreatedAt(time());
                    $last_rma_id = $rma->save()->getId();
                    $image_array = array();
                    $ext_array = array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG","bmp","BMP");
                    if($_FILES["related_images"]["tmp_name"][0] != ""){
                        $path = Mage::getBaseDir("media").DS."RMA".DS.$last_rma_id.DS;
                        $file->mkdir($path);
                        foreach($_FILES["related_images"]["tmp_name"] as $key => $value){
                            $ext = explode(".",$_FILES["related_images"]["name"][$key]);
                            if(in_array(end($ext), $ext_array)){
                                $new_image_name = time().$_FILES["related_images"]["name"][$key];
                                move_uploaded_file($value, $path.$new_image_name);
                                $image_array[$new_image_name] = $_FILES["related_images"]["name"][$key];
                            }
                            else
                                $error = true;
                        }
                    }
                    Mage::getModel("rmasystem/rma")->load($last_rma_id)->setImages(serialize($image_array))->save();
                    foreach($post["item_checked"] as $key => $item) {
                        Mage::getModel("rmasystem/items")
                            ->setRmaId($last_rma_id)
                            ->setItemId($key)
                            ->setReasonId($post["item_reason"][$key])
                            ->setQty($post["return_item"][$key])
                            ->save();
                    }
                    $bar_code = new Barcode39($post["increment_id"]);
                    $bar_code->barcode_text_size = 5; 
                    $bar_code->barcode_bar_thick = 4; 
                    $bar_code->barcode_bar_thin = 2;
                    $bar_code_path = Mage::getBaseDir("media")."/RMA/Barcodes/";
                    $file->mkdir($bar_code_path);
                    $bar_code->draw($bar_code_path.$last_rma_id.".gif");
                    if($last_rma_id > 0)
                        Mage::getModel("rmasystem/mails")->NewRma($post,$last_rma_id,$rma->getGroup());
                    if($error == true)
                        Mage::getSingleton("core/session")->addNotice($this->__("All files may not be uploaded"));
                    Mage::getSingleton("core/session")->addSuccess($this->__("RMA Saved Successfully"));
                    $this->_redirect("*/guest");
                }
                else{
                    Mage::getSingleton("core/session")->addError($this->__("Unable to save"));
                    $this->_redirect("*/guest/new");
                }
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Please Login"));
                $this->_redirect("*/*/");
            }
        }

        public function viewAction(){
            $id = $this->getRequest()->getParam("id");
            $data = Mage::getSingleton("core/session")->getGuestData();
            $rma = Mage::getModel("rmasystem/rma")->load($id);
            if($rma->getGuestEmail() == $data["email"] && isset($data)){
                $this->loadLayout(array("default","rmasystem_guest_view"));
                $this->getLayout()->getBlock("head")->setTitle($this->__("RMA Details"));
                $this->renderLayout();
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Sorry You Are Not Authorised to view this RMA request"));
                $this->_redirect("*/guest/rmalist");
            }
        }

        protected function printAction(){
            $id = $this->getRequest()->getParam("id");
            $data = Mage::getSingleton("core/session")->getGuestData();
            $rma = Mage::getModel("rmasystem/rma")->load($id);
            if($rma->getGuestEmail() == $data["email"] && isset($data)){
                $this->loadLayout(array("default","rmasystem_guest_print"));
                $this->getLayout()->getBlock("head")->setTitle($this->__("RMA Details"));
                $this->renderLayout();
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Sorry You Are Not Authorised to print this RMA request"));
                $this->_redirect("*/guest/rmalist");
            }
        }

        protected function printlabelAction(){
            $id = $this->getRequest()->getParam("id");
            $data = Mage::getSingleton("core/session")->getGuestData();
            $rma = Mage::getModel("rmasystem/rma")->load($id);
            if($rma->getGuestEmail() == $data["email"] && isset($data) && $rma->getShippingLabel() > 0){
                $this->loadLayout(array("default","rmasystem_guest_printlabel"));
                $this->getLayout()->getBlock("head")->setTitle($this->__("Pre Shipping Label"));
                $this->renderLayout();
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Shipping Label Not Available"));
                $this->_redirect("*/guest/rmalist");
            }
        }

        protected function cancelAction(){
            $id = $this->getRequest()->getParam("id");
            $data = Mage::getSingleton("core/session")->getGuestData();
            $rma = Mage::getModel("rmasystem/rma")->load($id);
            if($rma->getGuestEmail() == $data["email"] && isset($data)){
                $rma->setStatus(5)->save();
                Mage::getModel("rmasystem/mails")->CancelRma($id,$rma->getGroup());
                Mage::getSingleton("core/session")->addSuccess($this->__("RMA with id ").$id.$this->__(" has been cancelled successfully"));
                $this->_redirect("*/guest/rmalist");
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Sorry You Are Not Authorised to cancel this RMA request"));
                $this->_redirect("*/guest/rmalist");
            }
        }

        protected function updatermaAction(){
            $post = $this->getRequest()->getPost();
            $data = Mage::getSingleton("core/session")->getGuestData();
            if(isset($data)){
                if($post){
                    $image_error = false;$is_rma_save_required = false;$status_flag = false;$delivery_flag = false;
                    $rma = Mage::getModel("rmasystem/rma")->load($post["rma_id"]);
                    if($post["message"] != ""){
                        Mage::getModel("rmasystem/conversation")
                            ->setRmaId($post["rma_id"])
                            ->setMessage($post["message"])
                            ->setCreatedAt(time())
                            ->setSender($data["email"])
                            ->save();
                    }
                    else
                        Mage::getSingleton("core/session")->addError($this->__("Unable to save Message"));
                    if(isset($post["solved"])){
                        $rma->setStatus(4);
                        $is_rma_save_required = true;
                        $status_flag = true;
                    }
                    if(isset($post["pending"])){
                        $rma->setStatus(1);
                        $is_rma_save_required = true;
                        $status_flag = true;
                    }
                    if($rma->getCustomerConsignmentNo() != $post["customer_consignment_no"]){
                        $rma->setCustomerConsignmentNo($post["customer_consignment_no"]);
                        $is_rma_save_required = true;
                        $delivery_flag = true;
                    }
                    if($_FILES["related_images"]["tmp_name"][0] != ""){
                        $file = new Varien_Io_File();
                        $image_array = unserialize($rma->getImages());
                        $ext_array = array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG","bmp","BMP");
                        $path = Mage::getBaseDir("media").DS."RMA".DS.$rma->getId().DS;
                        $file->mkdir($path);
                        foreach($_FILES["related_images"]["tmp_name"] as $key => $value){
                            $ext = explode(".",$_FILES["related_images"]["name"][$key]);
                            if(in_array(end($ext), $ext_array)){
                                $new_image_name = time().$_FILES["related_images"]["name"][$key];
                                move_uploaded_file($value, $path.$new_image_name);
                                $image_array[$new_image_name] = $_FILES["related_images"]["name"][$key];
                            }
                            else
                                $image_error = true;
                        }
                        $rma->setImages(serialize($image_array));
                        $is_rma_save_required = true;
                    }
                    if($is_rma_save_required == true)
                        $rma->save();
                    if($status_flag == true || $delivery_flag == true)
                        Mage::getModel("rmasystem/mails")->RMAUpdate($post,$status_flag,$delivery_flag,$rma->getGroup());
                    else
                        Mage::getModel("rmasystem/mails")->NewMessage($post,$rma->getGroup(),"front");
                    if($image_error == true)
                        Mage::getSingleton("core/session")->addError($this->__("All files may not be uploaded"));
                    Mage::getSingleton("core/session")->addSuccess($this->__("RMA Successfully Updated"));
                    $this->_redirect("*/guest/view/", array("id" => $post["rma_id"]));
                }
                else{
                    Mage::getSingleton("core/session")->addError($this->__("Unable to save"));
                    $this->_redirect("*/guest/view",array("id",$post["rma_id"]));
                }
            }
            else{
                Mage::getSingleton("core/session")->addError($this->__("Please Login"));
                $this->_redirect("*/*/");
            }
        }

        public function logoutAction(){
            Mage::getSingleton("core/session")->unsGuestData();
            $this->_redirect("*/guest");
        }

    }