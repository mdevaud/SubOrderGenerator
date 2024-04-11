<?php

namespace SubOrderGenerator\EventListeners;

use SubOrderGenerator\Service\SubOrderService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;

class OrderStatusListener implements EventSubscriberInterface
{
    public function __construct(
        private SubOrderService $subOrderService,
        private EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function postOrderUpdate(OrderEvent $event){
        $order = $event->getOrder();
        if($this->subOrderService->isSubOrder($order->getId()) && $order->isPaid()){
            $parentOrder = $this->subOrderService->updateParentOrderStatus($order->getId(), OrderStatus::CODE_PAID);
            $event = new OrderEvent($parentOrder);
            $event->setStatus(OrderStatusQuery::getPaidStatus()->getId());
            $this->eventDispatcher->dispatch($event, TheliaEvents::ORDER_UPDATE_STATUS);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_UPDATE_STATUS => ['postOrderUpdate']
        ];
    }
}