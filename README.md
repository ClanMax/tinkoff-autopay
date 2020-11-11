# Tinkoff Autopay

Small library which help you to use methods from [Tinkoff API Documentation](https://oplata.tinkoff.ru/develop/api/autopayments/).

It was made for Laravel 7.0, but basically it should work almost everywhere even not only with Laravel apps.

It use some of [Laravel-Tinkoff](https://github.com/kenvel/laravel-tinkoff) functions inside.

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

- `Init`
- `AddCustomer`
- `GetCustomer`
- `RemoveCustomer`
- `GetCardList`
- `RemoveCard`
- `Charge`

This library not implement `Init` method in version 0.01. But it's implemented in 0.02.

Probably in some close future I will add `FinishAuthorize` method, but not sure right now.

## What in plans

1. `FinishAuthorize` probably will work
2. Right now library not return additional values of users (email, IP, etc). Will fix it.

## How does it work

Install library from composer

```bash
composer require clanmax/tinkoff-autopayment
```

Connect library with you controller by using `use`

```php
use ClanMax\TinkoffAutopay;
```

## Logic

Step by step how to use `Charge`

### Preparing

1. Add customer
2. Request `Init` *(not included in 0.01)* with `CustomerKey` and `Recurrent` parameters
3. Redirect user to payment form from `PaymentURL` value

### Charging

1. Request `Init` *(not included in 0.01)* without `CustomerKey` and `Recurrent` parameters
2. Save `PaymentID`
3. Request `GetCardList` and take from card which you want to use `RebillId`
4. Request `Charge` with `PaymentID` and `RebillId`

In this case user will be charged automatically and payment will be approved instantly.

## How to use

Make sure you already have Terminal Key and Terminal Password from you bank account. For using `FinishAuthorize` method you can get public key when switch you terminal working mode to Mobile application.

```json
$terminalKey = "16009807012222DEMO"; // demo may help to deploy
$terminal_password = "password";
$terminalurl = "https://securepay.tinkoff.ru/v2/";

$bank = new TinkoffAutopay($terminalurl,$terminalKey,$terminal_password);
```

### Initial payment

Make `POST` request to `Init` with two additional parameters:

```json
{
"Recurrent": "Y",
"CustomerKey": "clanmax"
}
```

Where `CustomerKey` name of already added client and `Recurrent` just with `Y`

For example:

```php
$payment = [
  'OrderId'       => '2223',
  'Amount'        => '1000',
  'Language'      => 'ru',
  'Reccurent'     => 'Y',
  'CustomerKey'   => "clanmax@me.ru",
  'Description'   => 'One month pay',
  'Email'         => 'me@email.com',
  'Phone'         => '+79517474837',
  'Name'          => 'Vlad',
  'Taxation'      => 'usn_income'
];

$items[] = [
  'Name'  => 'Something you gonna pay',
  'Price' => '1000',    
  'NDS'   => 'none',
];

$bank->Init($payment,$items);
```

Return will look like this:

```json
{
	'Success' => '1',
	'Status' => 'NEW',
	'PaymentURL' => "https://securepay.tinkoff.ru/new/58NbcI0s",
	'PaymentId' => '360127329'
}
```

### Charge money

Few steps:

1. Make `POST` request to Init, but without `Recurrent` and `CustomerKey`
2. Grab `PaymentId` from there

```php
$charge = $bank->Charge($PaymentId,$RebillId);
```

### Add customer

You have to make customer always before he did pay. One customer may have lots of connected cards by `Init` method.

```php
$customer = $bank->AddCustomer($CustomerKey);
```

### Get customer

Return existing customers. You have to have his name which you used before.

```php
$customer = $bank->GetCustomer($CustomerKey);
```

### Remove customer

Just removing all data of user. Be sure you won't use `RebillId` of this user anymore.

```php
$customer = $bank->GetCustomer($CustomerKey);
```

### Get card list

You will use this method for get `RebillId`. Return bunch of cards which user saved by `Init` method.

```php
$cards = $bank->GetCardList($CustomerKey);
```

### Remove card

If your client have tons of cards but you are not ready to use them instead of delete user you may just delete card. Just get card number from `GetCardList`

```php
$cards = $bank->RemoveCustomer($CardId, $CustomerKey);
```

## Find errors

I would recommend always check all request for errors by this way

```php
$bank->error ?:  
```

`Error` keep some information and you may use it for show to user (but be aware).
