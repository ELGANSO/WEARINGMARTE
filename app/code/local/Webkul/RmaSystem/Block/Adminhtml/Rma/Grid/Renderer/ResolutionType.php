<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_Grid_Renderer_ResolutionType extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $row['resolution_type'] == Webkul_RmaSystem_Model_Constants::ResolutionTypeRefund
            ? 'Devolución'
            : 'Cambio de talla';
    }
}