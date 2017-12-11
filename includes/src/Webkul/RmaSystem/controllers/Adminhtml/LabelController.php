<?php

    class Webkul_RmaSystem_Adminhtml_LabelController extends Mage_Adminhtml_Controller_Action {

        protected function _initAction() {
            $this->loadLayout()->_setActiveMenu("rmasystem");
            return $this;
        }

        public function indexAction() {
            $this->_initAction()->renderLayout();
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("rmasystem/label")->load($id);
            if ($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if(!empty($data))
                    $model->setData($data);
                Mage::register("label_data", $model);
                $this->loadLayout();
                $this->_setActiveMenu("rmasystem");
                $this->_addContent($this->getLayout()->createBlock("rmasystem/adminhtml_label_edit"))
                        ->_addLeft($this->getLayout()->createBlock("rmasystem/adminhtml_label_edit_tabs"));
                $this->renderLayout();
            } 
            else {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        }

        public function newAction() {
            $this->_forward("edit");
        }

        public function saveAction() {
            $imagedata = array();
            $data = $this->getRequest()->getPost();
            if (!empty($_FILES["filename"]["name"]) || ($data["filename"]["value"] != "" && !isset($data["filename"]["delete"]))) {
                if (!empty($_FILES["filename"]["name"])) {
                try {
                    $ext = substr($_FILES["filename"]["name"], strrpos($_FILES["filename"]["name"], ".") + 1);
                    $fname = "File-" . time() . "." . $ext;
                    $uploader = new Varien_File_Uploader("filename");
                    $uploader->setAllowedExtensions(array("jpg", "jpeg", "gif", "png"));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir("media").DS."RMA".DS."Labels";
                    $uploader->save($path, $fname);
                    $imagedata["filename"] = "RMA/Labels/".$fname;
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost()) {
                if (!empty($imagedata["filename"]))
                    $data["filename"] = $imagedata["filename"];
                else {
                    if (isset($data["filename"]["delete"]) && $data["filename"]["delete"] == 1) {
                        if ($data["filename"]["value"] != "")
                            $this->removeFile(Mage::getBaseDir("media").DS.Mage::helper("rmasystem")->updateDirSepereator($data["filename"]["value"]));
                        $data["filename"] = "";
                    }
                    else
                        unset($data["filename"]);
                }
                $model = Mage::getModel("rmasystem/label");
                $model->setData($data)->setId($this->getRequest()->getParam("id"));
                try {
                    $model->save();
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setFormData(false);
                    if ($this->getRequest()->getParam("back")) {
                        $this->_redirect("*/*/edit", array("id" => $model->getId()));
                        return;
                    }
                    $this->_redirect("*/*/");
                    return;
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    Mage::getSingleton("adminhtml/session")->setFormData($data);
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
            }
            Mage::getSingleton("adminhtml/session")->addError($this->__("Unable to find item to save"));
            $this->_redirect("*/*/");
            }else{
                 Mage::getSingleton('adminhtml/session')->addError("Please upload an image for shipping label");
                 $this->_redirect('*/*/new');  
            }
            
        }

        public function deleteAction() {
            if ($this->getRequest()->getParam("id") > 0) {
                try {
                    $model = Mage::getModel("rmasystem/label")->load($this->getRequest()->getParam("id"));
                    if($model->getFilename()!==null && $model->getFilename()!==0 && $model->getFilename()!==''){
                        $filePath = Mage::getBaseDir("media").DS.Mage::helper("rmasystem")->updateDirSepereator($model->getFilename());
                        $this->removeFile($filePath);
                    }
                    $model->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully deleted"));
                    $this->_redirect("*/*/");
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                }
            }
            $this->_redirect("*/*/");
        }

        public function massDeleteAction() {
            $Ids = $this->getRequest()->getParam("ids");
            if (!is_array($Ids))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item (s)"));
            else {
                try {
                    foreach ($Ids as $id) {
                        $model = Mage::getModel("rmasystem/label")->load($id);
                        if($model->getFilename()!==null && $model->getFilename()!==0 && strlen($model->getFilename())!==0)
                        {
                            $filePath = Mage::getBaseDir("media").DS.Mage::helper("rmasystem")->updateDirSepereator($model->getFilename());
                            $this->removeFile($filePath);
                        }
                            $model->delete();
                    }
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Total of %d record(s) were successfully deleted", count($Ids)));
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function massStatusAction() {
            $Ids = $this->getRequest()->getParam("ids");
            if (!is_array($Ids))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item (s)"));
            else {
                try {
                    foreach ($Ids as $id) {
                        Mage::getSingleton("rmasystem/label")->load($id)->setStatus($this->getRequest()->getParam("status"))->setIsMassupdate(true)->save();
                    }
                    $this->_getSession()->addSuccess($this->__("Total of %d record(s) were successfully updated", count($Ids)));
                }
                catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function exportCsvAction() {
            $fileName = "label.csv";
            $content = $this->getLayout()->createBlock("rmasystem/adminhtml_label_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "label.xml";
            $content = $this->getLayout()->createBlock("rmasystem/adminhtml_label_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
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

        protected function removeFile($file) {
            try {
                $io = new Varien_Io_File();
                $result = $io->rmdir($file, true);
            }
            catch (Exception $e) {}
        }

    }