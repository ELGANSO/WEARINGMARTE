<?php

    class Webkul_RmaSystem_Adminhtml_RmaController extends Mage_Adminhtml_Controller_Action {

        public function newAction()
        {
            Mage::register('rma_order_id', $this->getRequest()->getParam('order_id'));
            $this->loadLayout()->_setActiveMenu('rmasystem');
            $this->renderLayout();
        }

        public function postNewAction()
        {
            $rmaHelper = Mage::helper('rmasystem/rma');
            $orderId = $this->getRequest()->getPost('order_id');
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($orderId);
            $customerId = $order->getCustomerId();
            $postData = $this->getRequest()->getPost();

            $rma = $rmaHelper->saveRma($postData, $_FILES, $error, $customerId, $displayLabel);
            if($rma)
            {
                // Si se ha marcado la casilla de aprobación automática aprobamos la devolución
                if(@$postData['autoapprove'] == 1)
                {
                    $approvalHelper = Mage::helper('rmasystem/approval');
                    $approvalHelper->approveRma($rma);
                }
                Mage::getSingleton('adminhtml/session')->addNotice($this->__('Devolución generada correctamente.'));
                $this->_redirect('rmasystem/adminhtml_rma/index');
            }
            else
            {
                Mage::getSingleton('adminhtml/session')->addError($this->__('No se ha podido generar la devolución.'));
                $this->_redirect('*/*/new', [ 'order_id' => $orderId ]);
            }
        }

        public function validateAction()
        {
            $post = $this->getRequest()->getParams();
            $post['rma_id'] = $post['rma'];

            // Cargamos el RMA
            $rmaId = $this->getRequest()->getParam('rma');
            $rma = Mage::getModel('rmasystem/rma')->load($rmaId);

            // Añadimos el posible mensaje
            $this->addMessageToRma();

            // Aprobamos el RMA
            $helper = Mage::helper('rmasystem/approval');
            $helper->approveRma($rma);

            // Mandamos los e-mails
            Mage::getModel("rmasystem/mails")->RMAUpdateAdmin($post, true, false);

            // Redirigimos al listado de RMAs
            Mage::getSingleton('adminhtml/session')->addNotice($this->__('La devolución ha sido aprobada'));
            $this->_redirect('*/*/index');
        }

        public function denyAction()
        {
            $post = $this->getRequest()->getParams();
            $post['rma_id'] = $post['rma'];

            // Cargamos el RMA pasado como argumento
            $rmaId = $this->getRequest()->getParam('rma');
            /** @var Webkul_RmaSystem_Model_Rma $rma */
            $rma = Mage::getModel('rmasystem/rma')->load($rmaId);

            // Añadimos el posible mensaje
            $this->addMessageToRma();

            // Denegamos la devolución y la guardamos
            $rma->deny()->save();

            // Mandamos los e-mails
            Mage::getModel("rmasystem/mails")->RMAUpdateAdmin($post, true, false);

            // Mostramos un mensaje al admin
            $message = $rma->isRefund()
                ? 'La devolución ha sido denegada'
                : 'El cambio de talla ha sido denegado';

            Mage::getSingleton('adminhtml/session')->addNotice($message);

            // Y redirigimos al listado
            $this->_redirect('*/*/index');
        }

        protected function _initAction() {
            $this->loadLayout()->_setActiveMenu("rmasystem");
            $this->getLayout()->getBlock("head")->setTitle($this->__("RMA System"));
            return $this;
        }

        public function indexAction() {
            $this->_initAction()->renderLayout();
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("rmasystem/rma")->load($id);
            if($model->getId() || $id == 0) {
                Mage::register("rma_data", $model);
                $this->loadLayout();
                $this->getLayout()->getBlock("head")->setTitle($this->__("RMA Request"));
                $this->_addContent($this->getLayout()->createBlock("rmasystem/adminhtml_rma_edit"))
                    ->_addLeft($this->getLayout()->createBlock("rmasystem/adminhtml_rma_edit_tabs"));
                $this->renderLayout();
            }
            else {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        }

        public function updateAction()
        {
            $post = $this->getRequest()->getParams();
            $rmaId = $post['rma_id'];

            $rma = Mage::getModel('rmasystem/rma')->load($rmaId);

            $this->addMessageToRma();

            Mage::getModel('rmasystem/mails')->NewMessage($post,$rma->getGroup(),'admin');

            /*if($status_flag == true || $delivery_flag == true)
                Mage::getModel("rmasystem/mails")->RMAUpdateAdmin($post,$status_flag,$delivery_flag);
            else
                Mage::getModel("rmasystem/mails")->NewMessage($post,$rma->getGroup(),"admin");
            Mage::getSingleton("adminhtml/session")->addSuccess($this->__("RMA Updated Successfully"));

            if($rma->getStatus() == Webkul_RmaSystem_Model_Constants::StatusAccepted)
            {
                $approvalHelper = Mage::helper('rmasystem/approval');
                $approvalHelper->approveRma($rma);
            }*/

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Mensaje enviado correctamente'));

            $this->_redirect('*/*/edit', ['id' => $rmaId]);
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

        public function exportCsvAction() {
            $fileName = "rma.csv";
            $content = $this->getLayout()->createBlock("rmasystem/adminhtml_rma_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "rma.xml";
            $content = $this->getLayout()->createBlock("rmasystem/adminhtml_rma_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function gridAction()    {
            $this->loadLayout();
            $this->getResponse()->setBody($this->getLayout()->createBlock("rmasystem/adminhtml_rma_grid")->toHtml());
        }

        private function addMessageToRma()
        {
            $postData = $this->getRequest()->getParams();
            $rmaId = isset($postData['rma_id'])
                ? $postData['rma_id']
                : $postData['rma'];
            $message = $postData['message'];

            if(strlen($message) > 0)
            {
                // Añadimos el mensaje a la conversación
                /** @var Webkul_RmaSystem_Model_Conversation $conversation */
                $conversation = Mage::getModel('rmasystem/conversation');
                $conversation->setRmaId($rmaId)
                    ->setMessage($message)
                    ->setCreatedAt(time())
                    ->setSender(0)
                    ->save();
            }
        }
    }

