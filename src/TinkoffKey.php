<?php

namespace ClanMax;

use ClanMax\TinkoffAutopay;

/**
* Class for getting token for Postman testing
*/
class TinkoffKey {

  private $payment;
  private $items;
  private $package;

  function __construct() {
    $this->payment = [
      'OrderId'       => '2234',
      'Amount'        => '1000',
      'Language'      => 'ru',
      'Recurrent'     => 'Y',
      'CustomerKey'   => "8",
      'Description'   => 'One month pay',
      'Email'         => 'me@clanmax.ru',
      'Phone'         => '+79518886766',
      'Name'          => 'Vlad Meriin',
      'Taxation'      => 'usn_income'
    ];

    $this->items[] = [
      'Name'  => 'Оплата месяца тренировок',
      'Price' => '1000',    //цена товара в рублях
      'Tax'   => 'none',  //НДС
    ];

    $this->package = new TinkoffAutopay("https://securepay.tinkoff.ru/v2/Charge", "login", "password");
  }

  public function generateArray($payment, $items) {
    $params = array(
      'OrderId'       => $payment['OrderId'],
      'Amount'        => $payment['Amount'] * $amount_multiplicator,
      'Language'      => $payment['Language'],
      'Recurrent'     => @$payment['Recurrent'],
      'CustomerKey'   => @$payment['CustomerKey'],


      'Description'   => $payment['Description'],
      'DATA' => [
        'Email'     => $payment['Email'],
        'Phone'     => $payment['Phone'],
        'Name'      => $payment['Name'],
        ],
        'Receipt' => [
          'Email'     => $payment['Email'],
          'Phone'     => $payment['Phone'],
          'Taxation'  => $payment['Taxation'],
          'Items'     => $payment['Items'],
          ],
        );

      return print_r($this->package->generateToken($params));
      }
    }
