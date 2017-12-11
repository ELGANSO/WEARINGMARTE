<?php

    class Webkul_RmaSystem_Block_Adminhtml_Reason_Grid extends Mage_Adminhtml_Block_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("reasongrid");
            $this->setUseAjax(true);
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("rmasystem/reason")->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _prepareColumns() {

            $this->addColumn("id", array(
                "header"    =>  $this->__("Id"),
                "align"     =>  "center",
                "width"     =>  "200px",
                "index"     =>  "id"
            ));

            $this->addColumn("reason", array(
                "header"    =>  $this->__("Reason"),
                "align"     =>  "left",
                "index"     =>  "reason"
            ));

            $this->addColumn("status", array(
                "header"    =>  $this->__("Status"),
                "align"     =>  "center",
                "type"      =>  "options",
                "align"     =>  "center",
                "width"     =>  "200px",
                "index"     =>  "status",
                "options"   =>  array("0" => $this->__("Disabled"), "1" => $this->__("Enabled"))
            ));

            $this->addExportType("*/*/exportCsv", $this->__("CSV"));
            $this->addExportType("*/*/exportXml", $this->__("XML"));
            return parent::_prepareColumns();
        }

        protected function _prepareMassaction() {
            $this->setMassactionIdField("id");
            $this->getMassactionBlock()->setFormFieldName("ids");
            $this->getMassactionBlock()->addItem("delete", array(
                "label"      => $this->__("Delete"),
                "url"        => $this->getUrl("*/*/massDelete"),
                "confirm"    => $this->__("Are you sure")."?"
            ));
            $this->getMassactionBlock()->addItem("status", array(
                "label"      => $this->__("Change status"),
                "url"        => $this->getUrl("*/*/massStatus", array("_current" => true)),
                "additional" => array("visibility" => array(
                                                        "name"   => "status",
                                                        "type"   => "select",
                                                        "class"  => "required-entry",
                                                        "label"  => $this->__("Status"),
                                                        "values" => array("0" => $this->__("Disabled"), "1" => $this->__("Enabled"))
                                                    )
                                )
            ));
            return $this;
        }

        public function getRowUrl($row) {
            return $this->getUrl("*/*/edit", array("id" => $row->getId()));
        }

        public function getGridUrl()    {
            return $this->getUrl("*/*/grid", array("_current" => true));
        }

    }