<?php

final class Aplazame_Aplazame_Api_Order
{
    /** @var Mage_Sales_Model_Order */
    private $orderModel;

    public function __construct(Mage_Sales_Model_Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    public function history(array $params, array $queryArguments)
    {
        if (!isset($params['order_id'])) {
            return Aplazame_Aplazame_ApiController::not_found();
        }

        $order = $this->orderModel->loadByIncrementId($params['order_id']);
        if (!$order->getId()) {
            return Aplazame_Aplazame_ApiController::not_found();
        }

        $page = (isset($queryArguments['page'])) ? $queryArguments['page'] : 1;
        $page_size = (isset($queryArguments['page_size'])) ? $queryArguments['page_size'] : 10;

        /** @var Mage_Sales_Model_Order[] $history_collection */
        $orders = $this->orderModel
            ->getCollection()
            ->addAttributeToFilter('customer_id', array('like'=>$order->getCustomerId()))
            ->setPage($page, $page_size)
        ;

        $historyOrders = array_map(array('Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder', 'createFromOrder'), $orders);

        return Aplazame_Aplazame_ApiController::collection($page, $page_size, $historyOrders);
    }
}
