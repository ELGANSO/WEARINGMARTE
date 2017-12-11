<?php

require_once Mage::getBaseDir('lib') . '/rmasystem/Barcode39.php';

class Webkul_RmaSystem_Helper_Rma extends Mage_Core_Helper_Abstract
{
    /**
     * @param array $postData
     * @param array $filesData
     * @param bool $errorSavingImages
     * @param null|int $customerId
     * @param $displayLabel
     * @return null|Webkul_RmaSystem_Model_Rma
     */
    public function saveRma(
        $postData,
        $filesData,
        &$errorSavingImages,
        $customerId = null,
        &$displayLabel)
    {
        if (!$postData)
        {
            // No se nos han pasado los datos del formulario, salimos directamente
            return null;
        }

        // Si no se ha pasado un cliente, obtenemos el ID del cliente validado
        if($customerId === null)
        {
            $customerId = Mage::getSingleton('customer/session')->getId();
        }

        // Guardamos el RMA
        $rma = static::saveRmaModel($postData, $customerId);
        if($rma->getId() == 0)
        {
            // No se ha podido crear, salimos
            return null;
        }

        // Guardamos las imágenes adjuntas
        $errorSavingImages = static::saveRmaImages($rma, $filesData);

        // Guardamos los items de la devolución
        static::saveRmaItems($rma, $postData);

        // Generamos el código de barras
        static::generateBarcode($postData['increment_id'], $rma->getId());

        // Mandamos e-mails al cliente y al admin
        Mage::getModel('rmasystem/mails')->NewRma($postData, $rma->getId(), $rma['group']);

        // Generamos el envío en SEUR
        if($rma->needsToBePickedUp())
        {
            $seurWs = Mage::getModel('rmasystem/pickupLabels_seurWebService');
            $seurWs->generateLabel($rma->getId(), $rma->getOrder(), $rma->getPickupDate());
            $displayLabel = true;
        }
        else
        {
            $displayLabel = false;
        }

        return $rma;
    }

    /**
     * @param array $postData
     * @param int $customerId
     * @return Webkul_RmaSystem_Model_Rma
     */
    private static function saveRmaModel($postData, $customerId)
    {
        $date = Mage::app()->getLocale()
            ->date($postData['pickup_date'])
            ->toString('y-MM-d');

        /** @var Webkul_RmaSystem_Model_Rma $rma */
        $rma = Mage::getModel('rmasystem/rma')
            ->setData('order_id', $postData['order_id'])
            ->setData('group', 'customer')
            ->setData('increment_id', $postData['increment_id'])
            ->setData('resolution_type', $postData['resolution_type'])
            ->setData('package_condition', $postData['package_condition'])
            ->setData('customer_id', $customerId)
            ->setData('additional_info', $postData['additional_info'])
            ->setData('customer_delivery_status', $postData['customer_delivery_status'])
            ->setData('customer_consignment_no', $postData['customer_consignment_no'])
            ->setData('status', Webkul_RmaSystem_Model_Constants::StatusPending)
            ->setData('created_at', time())
            ->setData('pickup_date', $date)
            ->setData('pickup_address', $postData['pickup_address'])
            ->setData('pickup_number', $postData['pickup_number'])
            ->setData('pickup_postcode', $postData['pickup_postcode'])
            ->setData('pickup_city', $postData['pickup_city'])
            ->setData('pickup_region', $postData['pickup_region'])
            ->setData('pickup_phone', $postData['pickup_phone']);

        // Por defecto el RMA tendrá el estado de "Pendiente", pero
        // si el pedido ya ha sido enviado lo ponemos como "Esperando el envío"
        // a la espera de que el cliente devuelva la mercancía
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($rma->getOrderId());
        if($order->hasShipments())
        {
            $rma->setStatus(Webkul_RmaSystem_Model_Constants::StatusWaitingShipment);
        }

        $rma->save();

        // Si se ha escrito algún mensaje lo añadimos a la conversación
        $message = @$postData['message'];
        if(strlen($message))
        {
            /** @var Webkul_RmaSystem_Model_Conversation $conversation */
            $conversation = Mage::getModel('rmasystem/conversation');
            $conversation
                ->setRmaId($rma->getId())
                ->setMessage($message)
                ->setCreatedAt(time())
                ->setSender(0)
                ->save();
        }
        return $rma;
    }

    /**
     * @param Webkul_RmaSystem_Model_Rma $rma
     * @param array $filesData
     * @return bool true si todas las imágenes se han guardado correctamente o false si se ha
     * producido algún error
     */
    private static function saveRmaImages(Webkul_RmaSystem_Model_Rma $rma, $filesData)
    {
        $allowedExtensions = ['jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP'];
        $file = new Varien_Io_File();
        $error = false;

        $rmaImages = [];
        if ($filesData['related_images']['tmp_name'][0] != '')
        {
            $path = Mage::getBaseDir('media') . DS . 'RMA' . DS . $rma->getId() . DS;
            $file->mkdir($path);
            foreach ($filesData['related_images']['tmp_name'] as $key => $value)
            {
                $ext = explode('.', $filesData['related_images']['name'][$key]);
                if (in_array(end($ext), $allowedExtensions))
                {
                    $newImageName = time() . $filesData['related_images']['name'][$key];
                    move_uploaded_file($value, $path . $newImageName);
                    $rmaImages[$newImageName] = $filesData['related_images']['name'][$key];
                }
                else
                {
                    $error = true;
                }
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $rma->setData('images', serialize($rmaImages))->save();

        return $error;
    }

    /**
     * @param Webkul_RmaSystem_Model_Rma $rma
     * @param array $postData
     */
    private static function saveRmaItems(Webkul_RmaSystem_Model_Rma $rma, $postData)
    {
        foreach ($postData['item_checked'] as $key => $item)
        {
            $rmaItem = Mage::getModel('rmasystem/items')
                ->setData('rma_id', $rma->getId())
                ->setData('item_id', $key)
                ->setData('reason_id', $postData['item_reason'][$key])
                ->setData('qty', $postData['return_item'][$key]);

            if ($rma['resolution_type'] == Webkul_RmaSystem_Model_Constants::ResolutionTypeExchange)
            {
                $requestedProductId = $postData['requested_size'][$key];
                if (strlen($requestedProductId) > 0)
                {
                    /** @var Mage_Catalog_Model_Product $product */
                    $product = Mage::getModel('catalog/product')->load($requestedProductId);

                    /** @noinspection PhpParamsInspection */
                    $rmaItem
                        ->setData('requested_product_id', $requestedProductId)
                        ->setData('requested_product_name', $product->getName())
                        ->setData('requested_product_size', $product->getAttributeText('size'));
                }
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $rmaItem->save();
        }
    }

    /**
     * @param int $orderIncrementId
     * @param int $rmaId
     */
    private static function generateBarcode($orderIncrementId, $rmaId)
    {
        $bar_code = new Barcode39($orderIncrementId);
        $bar_code->barcode_text_size = 5;
        $bar_code->barcode_bar_thick = 4;
        $bar_code->barcode_bar_thin = 2;
        $bar_code_path = Mage::getBaseDir("media") . "/RMA/Barcodes/";
        $file = new Varien_Io_File();
        $file->mkdir($bar_code_path);
        $bar_code->draw($bar_code_path . $rmaId . ".gif");
    }
}