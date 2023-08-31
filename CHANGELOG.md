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


## 0.0.0 - 2023-08-31

### Changed
- Fix bug wrong property name for statusDescription
