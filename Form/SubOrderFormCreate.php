<?php

namespace SubOrderGenerator\Form;

use SubOrderGenerator\SubOrderGenerator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Base\OrderQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\Order;

class SubOrderFormCreate extends BaseForm
{
    public static function getName()
    {
        return 'sub_order_form_creation';
    }

    protected function buildForm()
    {
        $modules = [];

        /** @var \CustomerFamily\Model\CustomerFamily $customerFamily */
        foreach (ModuleQuery::create()->filterByActivate(1)->findByCategory('payment') as $module) {
            $modules[$module->getTitle()] = $module->getCode();
        }
        $this->formBuilder
            ->add("amountAlreadyPaid", NumberType::class,
                [
                    'label' => Translator::getInstance()->trans('amount already paid', [], SubOrderGenerator::DOMAIN_NAME),
                    'constraints' => [
                        new Callback(function($value, ExecutionContextInterface $context){
                            $parentOrderId = $context->getRoot()->getData()['parentOrderId'];
                            /** @var Order $parent */
                            $parent = OrderQuery::create()->findOneById($parentOrderId);
                            if ($value > $parent->getTotalAmount()) {
                                $context->addViolation(
                                    Translator::getInstance()->trans('amount already paid can\'t be greater than total amount of parent order', [], SubOrderGenerator::DOMAIN_NAME)
                                );
                            }
                        })
                    ]
                ]
            )
            ->add("parentOrderId", TextType::class)
            ->add("authorizedPaymentOption",   ChoiceType::class,
                [
                    'choices' => $modules,
                    'multiple' => true,
                    'label' => Translator::getInstance()->trans('Authorized payment option', [], SubOrderGenerator::DOMAIN_NAME),
                ]
            );

    }
}