<?php

namespace Laraditz\Bayar\Data;

use Laraditz\Bayar\Models\BayarPayment;

use function GerbangBayar\Support\{referenceId};

class PaymentData extends AbstractData
{
    public function __construct(
        public string $currency,
        public int $amount, // smallest currency unit
        public string $returnUrl,
        public string $description,
        public array $customer,
        public ?string $callbackUrl = null,
        public ?string $merchantRefId = null,
        public ?array $extra = [],
    ) {
        // $this->merchantRefId = $this->merchantRefId  ?? referenceId();
    }
}
