<?php

    class Webkul_RmaSystem_Block_Adminhtml_Label_Grid extends Webkul_RmaSystem_Block_Adminhtml_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("labelGrid");
            $this->setDefaultSort("id");
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("rmasystem/label")->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _prepareColumns() {
            $this->addColumn("id", array(
                "header"    => $this->__("ID"),
                "align"     => "center",
                "width"     => "30px",
                "index"     => "id"
            ));

            $this->addColumn("filename", array(
                "header"    => $this->__("Shipping Label"),
                "align"     => "center",
                "index"     => "filename",
                "type"      => "label",
                "escape"    => true,
                "sortable"  => false,
                "filter"    => false
            ));

            $this->addColumn("title", array(
                "header"    => $this->__("Title"),
                "index"     => "title",
    			"align"     => "center"
            ));

            $this->addColumn("price", array(
                "header"    => $this->__("Price"),
                "type"      => "price",
                "width"     => "130px",
                "index"     => "price",
                "currency_code" => $this->_getStore()->getBaseCurrency()->getCode(),
                "align"     => "center"
            ));

            $this->addColumn("status", array(
                "header"    => $this->__("Status"),
                "align"     => "left",
                "width"     => "130px",
                "index"     => "status",
                "type"      => "options",
                "options"   => array(
                                1 => "Enabled",
                                2 => "Disabled"
                            )
            ));

            $this->addColumn("action", array(
                "header"    => $this->__("Action"),
                "width"     => "80",
                "type"      => "action",
                "getter"    => "getId",
                "actions"   => array(array(
                "caption"   => $this->__("Edit"),
                "url"       => array("base" => "*/*/edit"),
                "field"     => "id")),
                "filter"    => false,
                "sortable"  => false,
                "index"     => "stores",
                "is_system" => true
            ));

            $this->addExportType("*/*/exportCsv", $this->__("CSV"));
            $this->addExportType("*/*/exportXml", $this->__("XML"));
            return parent::_prepareColumns();
        }

        protected function _prepareMassaction() {
            $this->setMassactionIdField("ids");
            $this->getMassactionBlock()->setFormFieldName("ids");
            $this->getMassactionBlock()->addItem("delete", array(
                "label"     => $this->__("Delete"),
                "url"       => $this->getUrl("*/*/massDelete"),
                "confirm"   => $this->__("Are you sure?")
            ));

            $this->getMassactionBlock()->addItem("status", array(
                "label"         => $this->__("Change status"),
                "url"           => $this->getUrl("*/*/massStatus", array("_current" => true)),
                "additional"    => array(
                "visibility"    => array(
                "name"          => "status",
                "type"          => "select",
                "class"         => "required-entry",
                "label"         => $this->__("Status"),
                "values"        => array(
                                    1 => $this->__("Enabled"),
                                    2 => $this->__("Disabled")
                                )))
            ));
            return $this;
        }

        public function getRowUrl($row) {
            return $this->getUrl("*/*/edit", array("id" => $row->getId()));
        }

        protected function _getStore()    {
            $storeId = (int) $this->getRequest()->getParam("store", 0);
            return Mage::app()->getStore($storeId);
        }

    }