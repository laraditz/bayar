<?php

namespace Laraditz\Bayar\Providers;

use BadFunctionCallException;
use GerbangBayar\Atome\Atome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laraditz\Bayar\Data\PaymentData;
use Laraditz\Bayar\Data\PaymentResponseData;
use Laraditz\Bayar\Enums\PaymentStatus;
use Laraditz\Bayar\Models\BayarCallback;
use Laraditz\Bayar\Models\BayarPayment;
use Laraditz\Bayar\Models\BayarRequest;
use LogicException;
use Saloon\Contracts\Connector;
use Saloon\Http\Response;

abstract class AbstractProvider
{
    protected Connector $connector;

    protected ?string $driver = null;

    protected mixed $response;

    private ?string $redirectUrl;

    protected mixed $paymentResponseData = null;

    protected string $referenceId = 'id';

    protected string $paymentReferenceId = 'referenceId';

    protected string $redirectUrlParameter = 'redirectUrl';

    public string $callbackEvent = '';

    protected function createConnector(string $connectorClass, ...$args)
    {
        $this->connector = new $connectorClass(...$args);
    }

    protected function getConnector(): Atome
    {
        return $this->connector;
    }

    public function createPayment(PaymentData $payload): ?array
    {
        $payment = $this->createPaymentRecord($payload);

        if ($payment) {
            return [
                'id' => $payment->id,
                'merchant_ref_id' => $payment->merchant_ref_id,
                'expires_at' => $payment->expires_at?->toISOString(),
                'payment_url' => route('bayar.pay', ['payment' => $payment]),
            ];
        }

        return null;
    }

    public function makePayment(BayarPayment $payment): self
    {
        $payment->increment('hit');

        $payload = $this->formatPaymentPayload($payment);

        $this->validateCreatePayment($payload);

        $bayarRequest = $this->addRequestRecord('createPayment', $payload);

        $response = $this->getConnector()->createPayment($payload);

        if ($response->successful()) {
            $response = $this->result($response);
            $bayarRequest->update([
                'response' => is_array($response) ? $response : null
            ]);

            $this->setResponse($response);
            $this->setRedirectUrl($response);
        } else {
            $bayarRequest->update([
                'response_error' => $response->body()
            ]);

            throw new LogicException($response->body());
        }

        return $this;
    }

    private function addRequestRecord(string $action, array $payload = [])
    {
        return BayarRequest::create([
            'action' => $action,
            'request' => $payload,
        ]);
    }

    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    public function getPaymentReferenceId(): string
    {
        return $this->paymentReferenceId;
    }

    private function createPaymentRecord(PaymentData $payload)
    {
        return BayarPayment::create([
            'driver' => $this->getDriver(),
            'merchant_ref_id' => $payload->merchantRefId,
            'currency_code' => $payload->currency,
            'amount' => $payload->amount,
            'customer_name' => data_get($payload, 'customer.name'),
            'customer_email' => data_get($payload, 'customer.email'),
            'customer_phone' => data_get($payload, 'customer.phone'),
            'description' => $payload->description,
            'return_url' => $payload->returnUrl,
            'callback_url' => $this->getCallbackUrl(),
            'extra' => data_get($payload, 'extra'),
            'expires_at' => $this->linkExpiresAt(),
        ]);
    }

    private function validateCreatePayment(array $payload)
    {
        if (count($this->createPaymentRules()) > 0) {
            $this->validate($payload, $this->createPaymentRules());
        }
    }

    private function validate(array $payload, array $rules)
    {
        $validator = Validator::make($payload, $rules);

        throw_if($validator->fails(), InvalidArgumentException::class, 'The given data was invalid. ' . json_encode($validator->errors()->messages()));
    }

    public function createPaymentRules(): array
    {
        return [];
    }

    public function redirect()
    {
        throw_if(!$this->getRedirectUrl(), BadFunctionCallException::class, __('Missing redirect url.'));

        return new RedirectResponse($this->getRedirectUrl());
    }

    public function fireCallbackEvent()
    {
        if ($this->callbackEvent) {
            event(new $this->callbackEvent($this->getPaymentResponseData()));
        }

        return $this;
    }

    protected function getReturnUrl(BayarPayment $payment)
    {
        return config('bayar.direct_return') === true ? $payment->return_url : route('bayar.done', ['payment' => $payment]);
    }

    private function setRedirectUrl(mixed $response): void
    {
        $this->redirectUrl = data_get($response, $this->redirectUrlParameter) ?? data_get($response, Str::snake($this->redirectUrlParameter));
    }

    private function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    private function setResponse(mixed $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function formatPaymentPayload(BayarPayment $payment): array
    {
        $payload = collect($this->createPaymentData($payment))->reject(function (mixed $item) {
            return empty($item);
        });

        return $payload->toArray();
    }

    public function getCallbackUrl(): ?string
    {
        return route('bayar.callback', ['provider' => $this->getDriver()]);
    }

    private function linkExpiresAt()
    {
        return now()->addMinutes(config('bayar.link_expires_in'));
    }

    public function saveCallback(): self
    {
        if ($data = $this->getPaymentResponseData()) {
            BayarCallback::create([
                'driver' => $this->getDriver(),
                'data' => $data,
            ]);
        }

        return $this;
    }

    public function returnResponse(): PaymentResponseData
    {
        return $this->formattedPaymentResponseData();
    }

    public function processCallback(): void
    {
        $paymentResponse = $this->formattedPaymentResponseData();
        $bayarPayment = $this->getPaymentRecord($paymentResponse?->referenceId);

        if ($paymentResponse && $bayarPayment) {
            if (
                ($bayarPayment->payment_status === null || $bayarPayment->payment_status !== $paymentResponse->status)
                && $bayarPayment->payment_status !== PaymentStatus::Completed
            ) {
                $bayarPayment->update([
                    'payment_status' => $paymentResponse->status,
                    'payment_description' => $paymentResponse?->statusDescription,
                    'callback_response' => $paymentResponse?->metadata,
                ]);
            }
        }
    }

    public function setDriver(string $driver)
    {
        if (!empty($driver)) {
            $this->driver = $driver;
        }

        return $this;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function paymentResponseData(array $response, ?BayarPayment $payment = null): self
    {
        $this->setPaymentResponseData($response);

        return $this;
    }

    public function setPaymentResponseData(mixed $paymentResponseData)
    {
        return $this->paymentResponseData = $paymentResponseData;
    }

    public function getPaymentResponseData()
    {
        return $this->paymentResponseData;
    }

    public  function getPaymentRecord(string $referenceId): BayarPayment
    {
        return BayarPayment::where($this->getReferenceId(), $referenceId)->first();
    }

    /**
     * Dynamically handle calls to the provider class.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return Response
     *
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments): Response
    {
        throw_if(!method_exists(get_class($this->getConnector()), $name), BadMethodCallException::class);

        $bayarRequest = $this->addRequestRecord($name, $arguments);

        $response = $this->getConnector()->$name(...$arguments);

        if ($response->successful()) {
            return $response;
        } else {
            $bayarRequest->update([
                'response_error' => $response->body()
            ]);

            throw new LogicException($response->body());
        }
    }

    public function swapProperties(array $source, array $replace): array
    {
        $replace = collect($replace);

        return collect($source)
            ->dot()
            ->mapWithKeys(function (mixed $value, string $key) use ($replace) {
                $keyArr = explode('.', $key);

                if ((count($keyArr) === 1 || 2) && $replace->has($key)) {
                    return [$replace->get($key) => $value];
                } elseif (str($key)->startsWith('extra.')) {
                    return [str($key)->replaceFirst('extra.', '')->value => $value];
                } elseif (count($keyArr) > 2) {
                    $found = $replace->sole(function (string $newKey, string $oldKey) use ($key) {
                        return str($key)->startsWith($oldKey);
                    });

                    if ($found) {
                        $oldKey = array_search($found, $replace->toArray());
                        $newKey = $found . str_replace($oldKey, '', $key);

                        return [$newKey => $value];
                    }
                }

                return [$key => $value];
            })
            ->undot()
            ->toArray();
    }
}
