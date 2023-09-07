# Changelog

All notable changes to `laraditz/bayar` will be documented in this file

## 0.0.0 - 2023-08-30

- Initial release

### Added
- Setup laravel service provider and related laravel package files.
- Add `BayarPayment`, `BayarCallback`, `BayarRequest` models and related migrations.
- Add `BayarController` controller and view files.
- Add `PaymentStatus` enum.
- Add `ProviderInterface` contract.
- Add `PaymentData` and `PaymentResponseData` DTO.
- Add `AtomeCallbackReceived` event.
- Add `Atome` provider.


## 0.0.1 - 2023-08-31

### Changed
- Fix bug wrong property name for statusDescription

## 0.0.2 - 2023-09-07

### Changed
- Check request data on payment done page to determine redirect method.

## 0.0.3 - 2023-09-07

### Added
- Add `direct_return` to config so that can bypass package return url.

## 0.0.4 - 2023-09-07

### Added
- Add `callback_url` to config to overwrite default callback URL.

## 0.0.5 - 2023-09-07

### Changed
- Update callback logic to allow refund after paid.

## 0.0.6 - 2023-09-07

### Changed
- Fix bug on callback logic.
