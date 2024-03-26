<?php

namespace SubOrderGenerator\Hook;

use SubOrderGenerator\Model\SubOrderQuery;
use SubOrderGenerator\Service\SubOrderService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use TheliaSmarty\Template\SmartyParser;
use Thelia\Core\Template\Assets\AssetResolverInterface;

class BackHook extends BaseHook
{
    public function __construct(
        SmartyParser $parser,
        AssetResolverInterface $resolver,
        EventDispatcherInterface $eventDispatcher,
        private SubOrderService $subOrderService,
    )
    {
        parent::__construct($parser, $resolver, $eventDispatcher);
    }

    public function addSubOrder(HookRenderEvent $event ): void
    {
        $content = $this->render('sub-order-data.html');
        if ($content === null) return;
        $event->add($content);
    }

    public function isSubOrder(HookRenderEvent $event ): void
    {
        $orderId = $event->getArgument('order_id');
        if($this->subOrderService->isSubOrder($orderId)){
            $parentOrder = SubOrderQuery::create()->findOneBySubOrderId($orderId)->getOrderRelatedByParentOrderId();
            $content = $this->render('is-sub-order.html',[
                'parentRef' => $parentOrder->getRef(),
                'parentId' => $parentOrder->getId(),
            ]);
            $event->add($content);
        }
        return;
    }

    public function addSubOrderJs(HookRenderEvent $event ): void
    {
        $event->add($this->addJS('assets/js/sub-order.js'));
    }

    public static function getSubscribedHooks(): array
    {
        return [
            "order.tab-content" => [
                [
                    "type" => "back",
                    "method" => "addSubOrder"
                ]
            ],
            "order-edit.top" => [
                [
                    "type" => "back",
                    "method" => "isSubOrder"
                ]
            ],
            "order.edit-js" => [
                [
                    "type" => "back",
                    "method" => "addSubOrderJs"
                ]
            ]
        ];
    }
}