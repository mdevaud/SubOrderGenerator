<?php

namespace SubOrderGenerator\Controller\Api;

use Eurolam\Eurolam;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Controller\Front\BaseFrontOpenApiController;
use SubOrderGenerator\Model\SubOrder;
use SubOrderGenerator\Model\SubOrderQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Response;
use Symfony\Component\Validator\Constraints\Json;
use Thelia\Core\HttpFoundation\Request;


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
        $currentUser = $request->getSession()->getCustomerUser();
        $subOrder = SubOrderQuery::create()->filterByToken($token)
            ->findOne();

        if (null === $subOrder) {
            throw new NotFoundHttpException('SubOrder not found');
        }
        if ($subOrder->getOrder()->getCustomerId() === $currentUser->getId()) {
            throw new AccessDeniedHttpException('Not Authorized');
        }

        return new JsonResponse([
            'subOrderId' => $subOrder->getSubOrderId(),
            'parentOrderId' => $subOrder->getParentOrderId(),
            'token' => $subOrder->getToken(),
            'authorizedPaymentOption' => $subOrder->getAuthorizedPaymentOption()
        ]);
    }
}