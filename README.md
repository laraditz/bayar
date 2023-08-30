# Laravel Bayar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laraditz/bayar.svg?style=flat-square)](https://packagist.org/packages/laraditz/bayar)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/bayar.svg?style=flat-square)](https://packagist.org/packages/laraditz/bayar)
![GitHub Actions](https://github.com/laraditz/bayar/actions/workflows/main.yml/badge.svg)

Laravel Bayar is a multi-payment processing library for Laravel. It is easy to use and handle most of the tedious tasks when integrating with a payment gateway. You also can easily create a new payment gateway provider to use with this library package. 

## Installation

You can install the package via composer:

```bash
composer require laraditz/bayar
```

Then install the payment gateway provider that you wish to use. See [supported providers](#supported-providers) section to see list of all available providers. 
For example, if you want to use Atome payment gateway, you can simply install it as below:-
```bash
composer require gerbang-bayar/atome
```

Go to respective provider's repository to see how to complete the setup.

## Usage

To create a payment intent, first create a `PaymentData` object. Then pass it into the `createPayment` method.

Here is the data that you can pass when creating `PaymentData` object. The `extra` prorperties can be used to pass an extra properties needed and it must follows the same property name as in the payment gateway respective's API.

```php
public string $currency,
public int $amount, // smallest currency unit
public string $returnUrl,
public string $description,
public array $customer,
public ?string $callbackUrl = null,
public ?string $merchantRefId = null,
public ?array $extra = [],
```

To create payment and get the payment URL to be redirected to.

```php
use Laraditz\Bayar\Data\PaymentData;

$paymentData = new PaymentData(
    description: 'Purchase',
    currency: 'MYR',
    amount: 1000,
    returnUrl: 'https://returnurl.here',
    customer: [
        'name' => 'Raditz Farhan',
        'phone' => '6012345678',
        'email' => 'raditzfarhan@gmail.com'
    ],
    extra: [
        'shippingAddress' => [
            'countryCode' => 'MY',
            'lines' => [
                'No 1, Taman ABC',
                'Jalan DCEF'
            ],
            'postCode' => '12345'
        ],
        'items' => [
            [
                'itemId' => 'ITEMSKU',
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 1000,
            ]
        ]    
    ]
);

$bayar = \Bayar::driver('atome')->createPayment($paymentData);
```

Return example:

```
[
  "id" => "01h91mbatnwn27y4y5s88b783k"
  "merchant_ref_id" => null
  "expires_at" => "2023-08-29T21:54:13.000000Z"
  "payment_url" => "http://yourappurl.com/bayar/pay/01h91mbatnwn27y4y5s88b783k"
]
```

Redirect to the `payment_url` to proceed to payment page. Once done, you will be redirected to the `returnUrl`. 

## Callback

Callback event will be managed automatically by this package. Each providers have their own callback event to receives payment update from respective payment gateway provider. You just need to add a `listener` for the event. Refer to the provider package for more info.

## Supported Providers
Currently it has only one provider which is `Atome`, but the list will grows with time.
- [Atome](https://github.com/gerbang-bayar/atome)

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email raditzfarhan@gmail.com instead of using the issue tracker.

## Credits

-   [Raditz Farhan](https://github.com/laraditz)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Dependencies

- [Gerbang Bayar Support](https://github.com/gerbang-bayar/support).
- [Saloon](https://docs.saloon.dev/).
