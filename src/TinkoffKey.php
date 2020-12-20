<?php

namespace ClanMax;

use ClanMax\TinkoffAutopay;

/**
* Class for getting token for Postman testing
*/
class TinkoffKey {

  private $payment;
  private $items;

  /**
   * You may make your own data in this section or send everything to generateArray
   */
  function __construct() {
    $this->payment = [
      'OrderId'       => '2234',
      'Amount'        => '10',
      'Language'      => 'ru',
      'Recurrent'     => 'Y',
      'CustomerKey'   => "8",
      'Description'   => 'One month pay',
      'Email'         => 'me@clanmax.ru',
      'Phone'         => '+56565665',
      'Name'          => 'Vlad Meriin',
      'Taxation'      => 'usn_income'
    ];

    $this->items[] = [
      'Name'  => 'Оплата месяца тренировок',
      'Price' => '1000',    //цена товара в рублях
      'Tax'   => 'none',  //НДС
    ];

    return $this->generateArray($this->payment, $this->items);
  }

  /**
   * Taking everything out and make hash
   * @param  array $payment Payment details
   * @param  array $items   Items for Receipt
   * @return string          Token in sha256
   */
  public function generateArray($payment, $items) {
    $params = array(
      'OrderId'       => $payment['OrderId'],
      'Amount'        => $payment['Amount'] * 1000,
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
          'Items'     => $items,
          ],
        );

      return print_r($this->generateToken($params));
      }

      private function generateToken(array $args) {
        $token = '';
        $args['Password']    = "pass";
        $args['TerminalKey'] = "login";
        ksort($args);

        foreach ($args as $arg) {
          if (!is_array($arg)) {
            $token .= $arg;
          }
        }

        return hash('sha256', $token) . "\n";
      }
    }
