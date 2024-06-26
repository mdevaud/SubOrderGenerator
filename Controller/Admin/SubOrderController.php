<?php

namespace SubOrderGenerator\Controller\Admin;

use SubOrderGenerator\Form\SubOrderFormCreate;
use SubOrderGenerator\Model\SubOrderQuery;
use SubOrderGenerator\Service\SubOrderService;
use SubOrderGenerator\SubOrderGenerator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Mailer\MailerFactory;
use Thelia\Model\Base\OrderStatusQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\OrderStatus;

#[Route('/admin/module/SubOrder', name: 'sub_order_admin_')]
class SubOrderController extends BaseAdminController
{
    public function __construct(
        private MailerFactory $mailer
    )
    {
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createSubOrder(SubOrderService $subOrderService)
    {
        $subOrderForm = $this->createForm(SubOrderFormCreate::getName());
        try {
            $form = $this->validateForm($subOrderForm);
            $data = $form->getData();
            $subOrder = $subOrderService->createSubOrderFromParent($data);
            return $this->generateRedirectFromRoute(
                'admin.order.update.view',
                ['tab' => 'modules'],
                ['order_id' => $subOrder->getParentOrderId()]
            );
        } catch (FormValidationException $ex) {
            $message = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }
        if ($message !== false) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans('SubOrder Creation', [], SubOrderGenerator::DOMAIN_NAME),
                $message,
                $subOrderForm,
                $ex
            );
        }
        return $this->generateErrorRedirect($subOrderForm);
    }

    #[Route('/{token}/delete', name: 'delete', methods: ['DELETE'])]
    public function deleteSubOrder(string $token, Translator $translator)
    {
        if(null === $subOrder = SubOrderQuery::create()->findOneByToken($token)){
            throw new NotFoundHttpException($translator->trans(
                'Suborder not found',
                [],
                SubOrderGenerator::DOMAIN_NAME)
            );
        }
        $orderStatusCancelled = OrderStatusQuery::create()->findOneByCode(OrderStatus::CODE_CANCELED);

        $subOrder->getOrderRelatedBySubOrderId()
            ->setOrderStatus($orderStatusCancelled)
            ->save();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/{token}/send-mail', name: 'send_mail', methods: ['POST'])]
    public function sendMail(string $token, SubOrderService $subOrderService, Translator $translator)
    {
        if(null === $subOrder = SubOrderQuery::create()->findOneByToken($token)){
            throw new NotFoundHttpException($translator->trans(
                'Suborder not found',
                [],
                SubOrderGenerator::DOMAIN_NAME)
            );
        }

        $parentOrder = $subOrder->getOrderRelatedByParentOrderId();
        $customer = $parentOrder->getCustomer();
        $email = $this->mailer->createEmailMessage(
            SubOrderGenerator::SUBORDER_LINK_MESSAGE_NAME,
            [ConfigQuery::getStoreEmail() => ConfigQuery::getStoreName()],
            [$customer->getEmail() => $customer->getFirstname().' '.$customer->getLastname()],
            ['subOrderlink' => $subOrder]
        );
        $this->mailer->send($email);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

}