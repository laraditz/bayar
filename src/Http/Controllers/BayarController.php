<?php

namespace Laraditz\Bayar\Http\Controllers;

use Illuminate\Http\Request;
use Laraditz\Bayar\Events\AtomeCallbackReceived;
use Laraditz\Bayar\Models\BayarPayment;
use LogicException;

class BayarController extends Controller
{
    public function pay(BayarPayment $payment)
    {
        throw_if(!$payment->expires_at?->isFuture(), LogicException::class, 'Payment link has expired.');
        throw_if($payment->response, LogicException::class, 'Payment link no longer valid.');

        $bayar = \Bayar::driver($payment->driver)
            ->makePayment($payment);

        throw_if(!$payment->can_pay, LogicException::class, 'Invalid Payment URL');

        return $bayar->redirect();
    }

    public function done(BayarPayment $payment, Request $request)
    {
        $data = $request->all();

        $paymentData = \Bayar::driver($payment->driver)
            ->paymentResponseData($data, $payment)
            ->returnResponse();

        $method = $data && count($data) > 0 ? 'POST' : 'GET';

        return view('bayar::bayar.done', compact('payment', 'paymentData', 'data', 'method'));
    }

    public function callback(string $provider, Request $request)
    {
        \Bayar::driver($provider)
            ->paymentResponseData($request->all())
            ->fireCallbackEvent()
            ->saveCallback()
            ->processCallback();
    }
}
