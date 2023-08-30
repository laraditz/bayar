<?php

namespace Laraditz\Bayar\Data;

use Laraditz\Bayar\Enums\PaymentStatus;

class PaymentResponseData extends AbstractData
{
    public function __construct(
        public string $referenceId,
        public PaymentStatus $status,
        public ?string $statusDescription = null,
        public ?array $metadata = [],
    ) {
    }
}
