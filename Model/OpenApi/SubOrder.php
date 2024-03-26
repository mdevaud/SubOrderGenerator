<?php

namespace SubOrderGenerator\Model\OpenApi;


use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Model\Api\BaseApiModel;

#[Schema(
    description: "Sub order",
)]
class SubOrder extends BaseApiModel
{
    #[Property(type: "integer")]
    protected string $subOrderId;
    #[Property(type: "integer")]
    protected string $parentOrderId;

    #[Property(type: "string")]
    protected string $token;

    #[Property(
        type: "array",
        items: new Items(
            type: "string"
        )
    )]
    protected string $authorizedPaymentOption;

    public function getSubOrderId(): string
    {
        return $this->subOrderId;
    }

    public function setSubOrderId(string $subOrderId): SubOrder
    {
        $this->subOrderId = $subOrderId;
        return $this;
    }

    public function getParentOrderId(): string
    {
        return $this->parentOrderId;
    }

    public function setParentOrderId(string $parentOrderId): SubOrder
    {
        $this->parentOrderId = $parentOrderId;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): SubOrder
    {
        $this->token = $token;
        return $this;
    }

    public function getAuthorizedPaymentOption(): string
    {
        return $this->authorizedPaymentOption;
    }

    public function setAuthorizedPaymentOption(string $authorizedPaymentOption): SubOrder
    {
        $this->authorizedPaymentOption = $authorizedPaymentOption;
        return $this;
    }
}