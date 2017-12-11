<?php

class Webkul_RmaSystem_Block_Adminhtml_Rma_New_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('rmasystem');

        $orderId = Mage::registry('rma_order_id');
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('rma_form', ['legend' => $this->__('Detalles')]);

        $fieldset->addType(
            'orderitems',
            Mage::getConfig()->getBlockClassName('rmasystem/adminhtml_rma_new_renderer_orderitems'));

        // ID de pedido
        $fieldset->addField('order_id', 'hidden', [
            'value' => $orderId,
            'name'  => 'order_id'
        ]);

        // Increment ID de pedido
        $fieldset->addField('increment_id', 'hidden', [
            'value' => $order->getIncrementId(),
            'name' => 'increment_id'
        ]);

        // Package condition (fijo a 0)
        $fieldset->addField('package_condition', 'hidden', [
            'value' => '0',
            'name' => 'package_condition'
        ]);

        // Estado de entrega (fijo a 0)
        $fieldset->addField('customer_delivery_status', 'hidden', [
            'value' => '0',
            'name' => 'customer_delivery_status'
        ]);

        $fieldset->addField('resolution_type', 'radios', [
            'label' => $helper->__('Acción a realizar'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'resolution_type',
            'value' => '-',
            'values' => [
                [
                    'value' => (string) Webkul_RmaSystem_Model_Constants::ResolutionTypeRefund,
                    'label' => 'Devolución'
                ],
                [
                    'value' => (string) Webkul_RmaSystem_Model_Constants::ResolutionTypeExchange,
                    'label' => 'Cambio de talla'
                ]
            ]
        ]);

        $fieldset->addField('items', 'orderitems', [
            'label' => $helper->__('Productos'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ]);

        $fieldset->addField('additional_info', 'textarea', [
            'label' => $helper->__('Información adicional'),
            'name' => 'additional_info',
        ]);

        if($order->hasShipments())
        {
            // Añadimos campos para indicar la información de recogida
            $address = $order->getShippingAddress();
            $this->addPickupFields($form, $address);
        }
        else
        {
            // Si el pedido no ha sido enviado todavía, damos la opción de aprobar
            // la devolución automáticamente
            $fieldset->addField('autoapprove', 'checkbox', [
                'label' => $helper->__('Aprobar automáticamente'),
                'name' => 'autoapprove',
                'value' => 1
            ]);
        }

        return parent::_prepareForm();
    }

    public function _afterToHtml($html)
    {
        $base = Mage::getBaseUrl('js');

        return $html . "
            <script type=\"text/javascript\" src=\"$base/rmasystem/constants.js\"></script>
            <script type=\"text/javascript\" src=\"$base/rmasystem/admin/new_rma.js\"></script>
            <script type=\"text/javascript\" src=\"$base/rmasystem/jquery.min.js\"></script>
            <script type=\"text/javascript\">
                (function(){
                    var x = jQuery.noConflict();
                    var initialized = false;
                    varienGlobalEvents.attachEventHandler('showTab', function()
                    {
                        if(!initialized)
                        {
                            initialized = true;
                            RmaSystem.Admin.newRmaPage.initialize(x, document.getElementById('new_rma_form'));
                        }
                    });
                })();
            </script>
            ";
    }

    private function addPickupFields(Varien_Data_Form $form, Mage_Sales_Model_Order_Address $address)
    {
        $helper = Mage::helper('rmasystem');

        $fieldset = $form->addFieldset('rma_form_pickup', ['legend' => $this->__('Recogida')]);

        // Fecha de recogida
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('pickup_date', 'date', [
            'label' => $helper->__('Pickup date'),
            'name' => 'pickup_date',
            'required' => true,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => $dateFormatIso,
            'class' => 'validate-date'
        ]);

        // Dirección
        $fieldset->addField('pickup_address', 'text', [
            'label' => $helper->__('Address'),
            'name' => 'pickup_address',
            'required' => true,
            'value' => $address->getStreet2()
        ]);

        // Número
        $fieldset->addField('pickup_number', 'text', [
            'label' => $helper->__('Number'),
            'name' => 'pickup_number',
            'required' => true,
            'value' => $address->getStreet3()
        ]);

        // Código postal
        $fieldset->addField('pickup_postcode', 'text', [
            'label' => $helper->__('Zip/Postal Code'),
            'name' => 'pickup_postcode',
            'required' => true,
            'value' => $address->getPostcode()
        ]);

        // Ciudad
        $fieldset->addField('pickup_city', 'text', [
            'label' => $helper->__('City'),
            'name' => 'pickup_city',
            'required' => true,
            'value' => $address->getCity()
        ]);

        // Teléfono
        $fieldset->addField('pickup_phone', 'text', [
            'label' => $helper->__('Telephone'),
            'name' => 'pickup_phone',
            'required' => true,
            'value' => $address->getTelephone()
        ]);
    }
}