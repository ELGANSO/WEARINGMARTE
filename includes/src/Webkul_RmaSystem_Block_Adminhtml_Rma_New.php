<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'rmasystem';
        $this->_controller = 'adminhtml_rma';
        $this->_mode = 'new';
        $this->_updateButton('save', 'label', $this->__('Generar devolución'));
        $this->_updateButton('save', 'onclick', 'save()');
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        $this->_formScripts[] = "function save()
        {
            // Simulamos un click en el botón de submit
            // para que se ejecute onsubmit
            var form = document.getElementById('new_rma_form');
            var button = form.ownerDocument.createElement('input');
            button.style.display = 'none';
            button.type = 'submit';
            form.appendChild(button).click();
            form.removeChild(button);
        }";
    }

    public function getHeaderText()
    {
        return $this->__('Nueva devolución');
    }
}