<?php

    class Webkul_RmaSystem_Adminhtml_ReasonController extends Mage_Adminhtml_Controller_Action {

        protected function indexAction() {
            $this->loadLayout()->_setActiveMenu("rmasystem");
            $this->getLayout()->getBlock("head")->setTitle($this->__("RMA Reasons"));
            $this->renderLayout();
            return $this;
        }

        public function newAction() {
            $this->_forward("edit");
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("rmasystem/reason")->load($id);
            if($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if(!empty($data))
                    $model->setData($data);
                Mage::register("reason_data", $model);
                $this->loadLayout();
                $this->_setActiveMenu("rmasystem");
                $this->getLayout()->getBlock("head")->setTitle($this->__("RMA Reasons"));
                $this->_addContent($this->getLayout()->createBlock("rmasystem/adminhtml_reason_edit"))
                        ->_addLeft($this->getLayout()->createBlock("rmasystem/adminhtml_reason_edit_tabs"));
                $this->renderLayout();
            }
            else {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        }

        public function saveAction() {
            if($data = $this->getRequest()->getPost()) {
                $model = Mage::getModel("rmasystem/reason");
                $model->setData($data)->setId($this->getRequest()->getParam("id"));
                try{
                    $model->save();
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setFormData(false);
                    if($this->getRequest()->getParam("back")) {
                        $this->_redirect("*/*/edit", array("id" => $model->getId()));
                        return;
                    }
                    $this->_redirect("*/*/");
                    return;
                }
                catch(Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    Mage::getSingleton("adminhtml/session")->setFormData($data);
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
            }
            Mage::getSingleton("adminhtml/session")->addError($this->__("Unable to find item to save"));
            $this->_redirect("*/*/");
        }

        public function deleteAction() {
            if ($this->getRequest()->getParam("id") > 0) {
                try {
                    $model = Mage::getModel("rmasystem/reason")->load($this->getRequest()->getParam("id"));
                    $model->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully deleted"));
                    $this->_redirect("*/*/");
                }
                catch(Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                }
            }
            $this->_redirect("*/*/");
        }

        public function massDeleteAction() {
            $Ids = $this->getRequest()->getParam("ids");
            if(!is_array($Ids))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item (s)"));
            else {
                try {
                    foreach($Ids as $Id)
                        Mage::getModel("rmasystem/reason")->load($Id)->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Total of ").count($Ids).$this->__(" record(s) were successfully deleted"));
                }
                catch(Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/");
        }

        public function massStatusAction() {
            $Ids = $this->getRequest()->getParam("ids");
            if(!is_array($Ids))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item (s)"));
            else {
                try {
                    foreach($Ids as $Id)
                        Mage::getSingleton("rmasystem/reason")->load($Id)->setStatus($this->getRequest()->getParam("status"))->setIsMassupdate(true)->save();
                    $this->_getSession()->addSuccess($this->__("Total of ").count($Ids).$this->__(" record(s) were successfully updated"));
                }
                catch(Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/");
        }

        public function exportCsvAction() {
            $fileName = "rmareason.csv";
            $content = $this->getLayout()->createBlock("rmasystem/adminhtml_reason_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "rmareason.xml";
            $content = $this->getLayout()->createBlock("rmasystem/adminhtml_reason_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function gridAction()  {
            $this->loadLayout();
            $this->getResponse()->setBody($this->getLayout()->createBlock("rmasystem/adminhtml_reason_grid")->toHtml());
        }

        protected function _sendUploadResponse($fileName, $content, $contentType="application/octet-stream") {
            $response = $this->getResponse();
            $response->setHeader("HTTP/1.1 200 OK", "");
            $response->setHeader("Pragma", "public", true);
            $response->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
            $response->setHeader("Content-Disposition", "attachment; filename=" . $fileName);
            $response->setHeader("Last-Modified", date("r"));
            $response->setHeader("Accept-Ranges", "bytes");
            $response->setHeader("Content-Length", strlen($content));
            $response->setHeader("Content-type", $contentType);
            $response->setBody($content);
            $response->sendResponse();
            die;
        }

    }