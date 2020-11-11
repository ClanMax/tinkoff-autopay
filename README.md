Small library which help you to use methods from [Tinkoff API Documentation](https://oplata.tinkoff.ru/develop/api/autopayments/).

It was made for Laravel 7.0, but basically it should work almost everywhere even not only with Laravel apps.

It's using some of [Laravel-Tinkoff](https://github.com/kenvel/laravel-tinkoff) functions inside.

## What it's doing

Just basic things. For example, we can:

- Add customer
- Get customer data
- Remove customer
- Get card list of customer
- Remove customer cards
- Charge money

You have to be sure you already have permission to use this kind of function. You could ask your own manager in Tinkoff bank.

## Which methods supported

To be more clear it's work with:

- `AddCustomer`
- `GetCustomer`
- `RemoveCustomer`
- `GetCardList`
- `RemoveCard`
- `Charge`

This library not implement `Init` method which you should use for make payments. You have to make it by you own or use another library.

Probably in some close future I will add `FinishAuthorize` method, but not sure right now.

## How does it work

Install library from composer

```bash
composer install clanmax/tinkoff-autopayment
```

Connect library with you controller by using `use`

```php
use ClanMax\TinkoffAutopay;
```

## How to use

Make sure you already have Terminal Key and Terminal Password from you bank account. For using `FinishAuthorize` method you can get public key when switch you terminal working mode to Mobile application.

### Add new client

### Initial payment

Make `POST` request to `Init` with two additional parameters:

```json
{
"Recurrent": "Y",
"CustomerKey": "clanmax"
}
```

Where `CustomerKey` name of already added client and `Recurrent` just with `Y`

### Charge money

Few steps:

1. Make `POST` request to Init, but without `Recurrent` and `CustomerKey`
2. Grab `PaymentId` from there
