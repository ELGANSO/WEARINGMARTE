<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = "id";
        $this->_blockGroup = "rmasystem";
        $this->_controller = "adminhtml_rma";
        $this->_removeButton('save');
        $this->_removeButton("reset");
        $this->_removeButton("delete");

        /** @var Webkul_RmaSystem_Model_Rma $rma */
        $rma = Mage::registry('rma_data');

        if ($rma->isOpen())
        {
            // Añadimos un botón para aceptar la devolución
            $url = Mage::helper('adminhtml')->getUrl('rmasystem/adminhtml_rma/validate', ['rma' => $rma->getId()]);

            $this->_addButton('validate', [
                'label' => $rma->isRefund()
                    ? 'Aprobar devolución'
                    : 'Aprobar cambio de talla',
                'onclick' => sprintf("submitForm('%s');", $url),
                'class' => 'save',
                'disabled' => !$rma->canBeAccepted()
            ]);

            // Y otro para denegarla
            $url = Mage::helper('adminhtml')->getUrl('rmasystem/adminhtml_rma/deny', ['rma' => $rma->getId()]);

            $this->_addButton('deny', [
                'label' => $rma->isRefund()
                    ? 'Denegar devolución'
                    : 'Denegar cambio de talla',
                'onclick' => sprintf("submitForm('%s');", $url),
                'class' => 'save'
            ]);
        }

        $this->_formScripts[] = "function submitForm(url)
        {
            var form = document.getElementById('edit_form');
            form.action = url;
            form.submit();
        }";
    }

    public function getHeaderText()
    {
        return $this->__("View RMA id ") . $this->htmlEscape(Mage::registry("rma_data")->getIncrementId() . "-" . Mage::registry("rma_data")->getId());
    }

}