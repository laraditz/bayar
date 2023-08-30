<?php

namespace Laraditz\Bayar\Contracts;

use Laraditz\Bayar\Data\PaymentResponseData;
use Laraditz\Bayar\Enums\PaymentStatus;
use Laraditz\Bayar\Models\BayarPayment;
use Saloon\Http\Response;

interface ProviderInterface
{
    /**
     * Determine the expected result from the provider
     *
     * @return mixed
     */
    public function result(Response $response): mixed;

    /**
     * Create payment data to send to payment gateway
     *
     * @return array
     */
    public function createPaymentData(BayarPayment $payment): array;

    /**
     * Match payment status from payment gateway with PaymentStatus enum
     *
     * @return Laraditz\Bayar\Enums\PaymentStatus
     */
    public function paymentStatus(mixed $status): PaymentStatus;

    /**
     * Format the response from payment gateway to PaymentResponseData
     *
     * @return Laraditz\Bayar\Data\PaymentResponseData
     */
    public function formattedPaymentResponseData(): PaymentResponseData;
}
