<?php

namespace SubOrderGenerator\Smarty;

use Eurolam\Service\CampaignService;
use SubOrderGenerator\Model\SubOrderQuery;
use SubOrderGenerator\Service\SubOrderService;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class SubOrderPlugin extends AbstractSmartyPlugin
{
    public function __construct(private SubOrderService $subOrderService)
    {
    }

    public function getSubOrderList(array $params, $smarty){
        $subOrder = SubOrderQuery::create()->findOneBySubOrderId($params['orderId']);
        if(!$subOrder){
            return json_encode([]);
        }
        $history = $this->subOrderService->getHistoryPayment($subOrder);
//        dd($history);
        return json_encode($history);
    }

    public function getPluginDescriptors()
    {
        return [
            new SmartyPluginDescriptor(
                'function',
                'getSubOrderList',
                $this,
                'getSubOrderList'
            ),
        ];
    }
}
