<?php

namespace SubOrderGenerator\Command;


use SubOrderGenerator\Service\SubOrderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Model\Base\OrderQuery;

class testCommand extends ContainerAwareCommand
{
    public function __construct(protected SubOrderService $subOrderService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName("suborder:test")
            ->setDescription("test command");
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, OutputInterface $output): int
    {

        $this->subOrderService->createSubOrderFromParent([
            'parentOrderId' => 19,
            'amountAlreadyPaid' => -5,
            'authorizedPaymentOption' => ['test']
        ]);

        return Command::SUCCESS;
    }
}