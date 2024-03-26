<?php

namespace SubOrderGenerator\Form;

use SubOrderGenerator\SubOrderGenerator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;

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
            ->add("amountAlreadyPaid", TextType::class,
                [
                    'label' => Translator::getInstance()->trans('amount already paid', [], SubOrderGenerator::DOMAIN_NAME),
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