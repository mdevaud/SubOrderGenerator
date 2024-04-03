<?php

namespace SubOrderGenerator\EventListeners;

use SubOrderGenerator\Service\SubOrderService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\OrderStatus;

class OrderStatusListener implements EventSubscriberInterface
{
    public function __construct(
        private SubOrderService $subOrderService
    )
    {
    }

    public function postOrderUpdate(OrderEvent $event){
        $order = $event->getOrder();
        if($this->subOrderService->isSubOrder($order->getId()) && $order->isPaid()){
            $this->subOrderService->updateParentOrderStatus($order->getId(), OrderStatus::CODE_PAID);
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_UPDATE_STATUS => ['postOrderUpdate']
        ];
    }
}