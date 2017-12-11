<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId("rmagrid");
        $this->setDefaultSort("id");
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("rmasystem/rma")->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        $collection->getSelect()->join(array("ce1" => $prefix . "sales_flat_order_address"), "ce1.parent_id=main_table.order_id", array("fullname" => "CONCAT(ce1.firstname,' ',ce1.lastname)"))->where("ce1.address_type='billing'");
        $collection->addFilterToMap("fullname", "CONCAT(ce1.firstname,' ',ce1.lastname)");
        $collection->addFilterToMap("main_id", "main_table.id");
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn("id", array(
            "header" => $this->__("Id"),
            "align" => "center",
            "width" => "50px",
            "index" => "id",
            "filter_index" => "main_id"
        ));

        $this->addColumn("name", array(
            "header" => $this->__("Customer Name"),
            "align" => "center",
            "index" => "fullname",
            "filter_index" => "fullname"
        ));

        $this->addColumn("increment_id", array(
            "header" => $this->__("Order Id"),
            "align" => "center",
            "index" => "increment_id"
        ));

        $this->addColumn("resolution_type", array(
            "header" => $this->__("Tipo"),
            "align" => "center",
            "index" => "resolution_type",
            'renderer' => 'Webkul_RmaSystem_Block_Adminhtml_Rma_Grid_Renderer_ResolutionType',
            'type' => 'options',
            'options' => [
                Webkul_RmaSystem_Model_Constants::ResolutionTypeRefund => 'Devolución',
                Webkul_RmaSystem_Model_Constants::ResolutionTypeExchange => 'Cambio de talla',
            ]
        ));

        $this->addColumn("status", array(
            "header" => $this->__("RMA Status"),
            "align" => "center",
            "index" => "status",
            "type" => "options",
            "filter_index" => "main_table.status",
            "options" => Mage::getModel('rmasystem/rma')->getStatusOptions()
        ));

        $this->addColumn("created_at", array(
            "header" => $this->__("Date"),
            "align" => "left",
            "width" => "250px",
            "index" => "created_at",
            "type" => "datetime",
            "filter_index" => "main_table.created_at"
        ));

        $this->addExportType("*/*/exportCsv", $this->__("CSV"));
        $this->addExportType("*/*/exportXml", $this->__("XML"));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl("*/*/grid", array("_current" => true));
    }

    public function deliverystatus()
    {
        $status = array($this->__("Not Delivered Yet"), $this->__("Delivered"));
        return $status;
    }

}