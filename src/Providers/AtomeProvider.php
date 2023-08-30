<?php

namespace Laraditz\Bayar\Providers;

use GerbangBayar\Atome\Atome;
use GerbangBayar\Support\Traits\HasJsonResponse;
use Laraditz\Bayar\Contracts\ProviderInterface;
use Laraditz\Bayar\Data\PaymentResponseData;
use Laraditz\Bayar\Enums\PaymentStatus;
use Laraditz\Bayar\Events\AtomeCallbackReceived;
use Laraditz\Bayar\Models\BayarPayment;

class AtomeProvider extends AbstractProvider implements ProviderInterface
{
    use HasJsonResponse;

    public string $callbackEvent = AtomeCallbackReceived::class;

    public function __construct(array $config)
    {
        $this->createConnector(
            Atome::class,
            username: $config['username'],
            password: $config['password'],
            sandbox: $config['sandbox'],
        );
    }

    public function createPaymentData(BayarPayment $payment): array
    {
        $data =  [
            'referenceId' => $payment->id,
            'currency' => $payment->currency_code,
            'amount' => $payment->amount,
            'merchantReferenceId' => $payment->merchant_ref_id,
            'callbackUrl' => $payment->callback_url,
            'paymentResultUrl' => $this->getReturnUrl($payment),
            'customerInfo' => [
                'mobileNumber' => $payment->customer_phone,
                'fullName' => $payment->customer_name,
                'email' => $payment->customer_email,
            ],
        ];

        if (data_get($payment, 'extra')) {
            $data += data_get($payment, 'extra');
        }

        return $data;
    }

    public function createPaymentRules(): array
    {
        return [
            'referenceId' => 'required|string|min:5|max:40',
            'currency' => 'required|string',
            'amount' => 'required|integer',
            'callbackUrl' => 'required|string',
            'paymentResultUrl' => 'required|string',
            'customerInfo' => 'required|array',
            'customerInfo.mobileNumber' => 'required|string',
            'shippingAddress' => 'required|array|required_array_keys:countryCode,lines,postCode',
            'shippingAddress.countryCode' => 'required|string',
            'shippingAddress.lines' => 'required|array|min:1',
            'shippingAddress.postCode' => 'required|string',
            'billingAddress' => 'sometimes|array|required_array_keys:countryCode,lines,postCode',
            'billingAddress.countryCode' => 'sometimes|required|string',
            'billingAddress.lines' => 'sometimes|required|array|min:1',
            'billingAddress.postCode' => 'sometimes|required|string',
            'items' => 'required|array|min:1',
            'items.*.itemId' => 'required|string',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer',
            'items.*.price' => 'required|integer',
        ];
    }

    public function paymentStatus(mixed $status): PaymentStatus
    {
        return match ($status) {
            'PROCESSING' => PaymentStatus::Processing,
            'PAID' => PaymentStatus::Completed,
            'FAILED' => PaymentStatus::Failed,
            'REFUNDED' => PaymentStatus::Refunded,
            'CANCELLED' => PaymentStatus::Cancelled,
            default => PaymentStatus::Others
        };
    }

    public function paymentResponseData(array $response, ?BayarPayment $payment = null): self
    {
        if ($referenceId = data_get($response, $this->getPaymentReferenceId())) {
            $result = $this->result($this->getPayment($referenceId));
        } elseif ($payment) {
            $result = $this->result($this->getPayment($payment->{$this->getReferenceId()}));
        }

        if ($result) {
            $this->setPaymentResponseData($result);
        }

        return $this;
    }

    public function formattedPaymentResponseData(): PaymentResponseData
    {
        $paymentResponse = $this->getPaymentResponseData();

        $paymentStatus = data_get($paymentResponse, 'status');
        $paymentReferenceId = data_get($paymentResponse, $this->getPaymentReferenceId());

        $status = $paymentResponse ? $this->paymentStatus($paymentStatus) : PaymentStatus::Failed;

        return new PaymentResponseData(
            referenceId: $paymentReferenceId,
            status: $status,
            statusDescription: $paymentResponse ? data_get($paymentResponse, 'status') : null,
            metadata: $paymentResponse ? $paymentResponse : null
        );
    }
}
