<?php

namespace SubOrderGenerator\Controller\Api;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Controller\Front\BaseFrontOpenApiController;
use OpenApi\Service\OpenApiService;
use Propel\Runtime\Exception\PropelException;
use SubOrderGenerator\Model\SubOrderQuery;
use SubOrderGenerator\Service\SubOrderService;
use SubOrderGenerator\SubOrderGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Base\OrderProduct;
use Thelia\Model\ModuleQuery;


#[Route("/open_api/sub_order", name: "sub_order")]
class SubOrderController extends BaseFrontOpenApiController
{
    #[Route('/{token}', name: "get", methods: ["GET"])]
    #[Get(
        path: "/sub_order/{token}",
        summary: "Get sub_order item from token",
        tags: ["Eurolam routes"],
        parameters: [
            new Parameter(
                name: "token",
                description: "token of the sub order",
                in: "path",
                required: true
            )
        ],
        responses: [
            new Response(
                response: 200,
                description: 'Success',
                content: new JsonContent(
                    ref: "#/components/schemas/SubOrder"
                )
            ),
            new Response(
                response: 404,
                description: 'SubOrder not found',
                content: new JsonContent(
                    ref: "#/components/schemas/Error"
                )
            ),
            new Response(
                response: 403,
                description: 'Not Authorized',
                content: new JsonContent(
                    ref: "#/components/schemas/Error"
                )
            )

        ]
    )]
    public function view(
        $token,
        Request $request
    ): JsonResponse {
        $subOrder = SubOrderQuery::create()->filterByToken($token)
            ->findOne();

        if (null === $subOrder) {
            throw new NotFoundHttpException('SubOrder not found');
        }

        return new JsonResponse([
            'subOrder' => $subOrder->getOrderRelatedBySubOrderId()->toArray(),
            'token' => $subOrder->getToken(),
            'authorizedPaymentOption' => $subOrder->getAuthorizedPaymentOption()
        ]);
    }

    /**
     * @throws PropelException
     */
    #[Route("/{token}/to_cart", name: "sub_order_to_cart", methods: ["POST"])]
    #[Post(
        path: "/sub_order/{token}/to_cart",
        summary: "Fill a cart with product from a suborder and add discount",
        tags: ["SubOrder", "Eurolam routes"],
        parameters: [
            new Parameter(
                name: "token",
                description: "The suborder token",
                in: "path",
                required: true
            )
        ],
        responses: [
            new Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function fillCartWithSubOrder(
        $token,
        SubOrderService $subOrderService,
        Request $request,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $subOrder = SubOrderQuery::create()->findOneByToken($token);
        if (null === $subOrder) {
            throw new NotFoundHttpException(Translator::getInstance()->trans('Cette sous-commande n\'existe pas', [], SubOrderGenerator::DOMAIN_NAME));
        }
        $childOrder = $subOrder->getOrderRelatedBySubOrderId();
        $discountAmount = 0;
        /** @var OrderProduct $orderProduct */
        foreach ($childOrder->getOrderProducts() as $orderProduct){
            if ($orderProduct->getPrice() < 0){
                $discountAmount+=(float)$orderProduct->getPrice();
            }
        }
        $request->getSession()->clearSessionCart($eventDispatcher);

        $subOrderService->fillCartFromSubOrderChild($childOrder);
        $cart = $request->getSession()->getSessionCart();

        $request->getSession()->set(SubOrderGenerator::SUBORDER_TOKEN_SESSION_KEY, $subOrder->getToken());

        $cart->setDiscount($discountAmount*(-1))->save();

        $deliveryModule = ModuleQuery::create()->findPk($childOrder->getDeliveryModuleId());
        $customerId = $childOrder->getCustomerId();

        $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);
        $deliveryMode = $moduleInstance->getDeliveryMode();

        $deliveryAddress = $subOrderService->getCustomerAddressOrCreate($childOrder->getDeliveryOrderAddressId(), $customerId, $deliveryMode);
        $invoiceAddress = $subOrderService->getCustomerAddressOrCreate($childOrder->getInvoiceOrderAddressId(), $customerId);

        return OpenApiService::jsonResponse([
            'deliveryAddressId' => $deliveryAddress->getId(),
            'invoiceAddressId' =>  $invoiceAddress->getId(),
            'deliveryModuleId' => $deliveryModule->getId(),
            'deliveryModuleCode' => $deliveryModule->getCode(),
            'deliveryMode' => $deliveryMode
        ]);
    }
}