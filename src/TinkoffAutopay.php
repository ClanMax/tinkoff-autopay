<?php

namespace ClanMax;

/**
* Tinkoff charge library
*
* Documentation https://oplata.tinkoff.ru/landing/develop/documentation
*
* @link   https://github.com/ClanMax/tinkoff-autopay
* @version 0.02
* @author ClanMax <me@clanmax.ru>
*/
class TinkoffAutopay {

  private $tinkoff_url;
  private $terminal_key;
  private $password_key;

  private $url_charge;
  private $url_addcustomer;
  private $url_init;
  private $url_getcustomer;
  private $url_removecustomer;
  private $url_getcardlist;
  private $url_removecard;

  public $error;
  protected $response;
  protected $response_messages;

  function __construct($url, $terminalkey, $passwordkey) {
    $this->tinkoff_url = $url;
    $this->terminal_key = $terminalkey;
    $this->password_key = $passwordkey;
    $this->initURL();
  }

  /**
  * Using method Init. Documentation https://oplata.tinkoff.ru/develop/api/payments/init-description/
  * @param array $payment Basic information about payment
  * @param array $items   Information about what user wanna buy
  */
  public function Init(array $payment, array $items){
    $item_name_max_lenght = 64;
    $amount_multiplicator = 100;

    /**
    * Generate items array for Receipt
    */
    foreach ($items as $item) {

      $payment['Items'][] = [
        'Name'      => mb_strimwidth($item['Name'], 0, $item_name_max_lenght - 1, ''),
        'Price'     => $item['Price'] * $amount_multiplicator,
        'Quantity'  => $item['Amount'],
        'Amount'    => $item['Price'] * $amount_multiplicator,
        'PaymentObject' => @$item['PaymentObject'],
        'PaymentMethod' => @$item['PaymentMethod'],
        'Tax'       => $item['Tax'],
      ];
    }

    $params = array(
      'OrderId'       => $payment['OrderId'],
      'Amount'        => $payment['Amount'] * $amount_multiplicator,
      'Language'      => $payment['Language'],
      'Recurrent'     => @$payment['Recurrent'],
      'CustomerKey'   => @$payment['CustomerKey'],


      'Description'   => $payment['Description'],
      'DATA' => [
        'Email'     => @$payment['Email'],
        'Phone'     => @$payment['Phone'],
        'Name'      => $payment['Name'],
        ],
        'Receipt' => [
          'Email'     => @$payment['Email'],
          'Phone'     => @$payment['Phone'],
          'Taxation'  => $payment['Taxation'],
          'Items'     => $payment['Items'],
          ],
        );

        if( $this->sendRequest($this->url_init, $params) ){
          return $this->response_messages;
        }

      return false;
    }

    /**
    * Using method Charge. Documentation https://oplata.tinkoff.ru/develop/api/autopayments/charge-description/
    *
    * @param string $PaymentId Should get it from method Init
    * @param string $RebillId  You may get it from GetCardList
    */
    public function Charge($PaymentId, $RebillId) {
      $params = [
        'PaymentId' => $PaymentId,
        'RebillId' => $RebillId,
      ];

      if( $this->sendRequest($this->url_charge, $params) ){
        return $this->response_messages;
      }

      return false;
    }

    /**
    * Using method AddCustomer. Documentation https://oplata.tinkoff.ru/develop/api/autopayments/addcustomer-description/
    * @param string $CustomerKey Name of customer. You may may use any kind of symbols
    */
    public function AddCustomer($CustomerKey) {
      $params = [
        'CustomerKey' => $CustomerKey,
      ];

      if( $this->sendRequest($this->url_addcustomer, $params) ){
        return $this->response_messages;
      }

      return false;
    }

    /**
    * Using method GetCustomer. Documentation https://oplata.tinkoff.ru/develop/api/autopayments/getcustomer-description/
    * @param string $CustomerKey Name of existing customer
    */
    public function GetCustomer($CustomerKey) {
      $params = [
        'CustomerKey' => $CustomerKey,
      ];

      if( $this->sendRequest($this->url_getcustomer, $params) ){
        return $this->response_messages;
      }

      return false;
    }

    /**
    * Using method RemoveCustomer. Documentation https://oplata.tinkoff.ru/develop/api/autopayments/removecustomer-description/
    * @param string $CustomerKey Name of existing customer
    */
    public function RemoveCustomer($CustomerKey) {
      $params = [
        'CustomerKey' => $CustomerKey,
      ];

      if( $this->sendRequest($this->url_removecustomer, $params) ){
        return $this->response_messages;
      }

      return false;
    }

    /**
    * Using method GetCardList. Documentation https://oplata.tinkoff.ru/develop/api/autopayments/getcardlist-description/
    * @param string $CustomerKey Name of existing customer
    */
    public function GetCardList($CustomerKey) {
      $params = [
        'CustomerKey' => $CustomerKey,
      ];

      if( $this->sendRequest($this->url_getcardlist, $params) ){
        return $this->response_messages;
      }

      //return false;
      return $this->error;
    }

    /**
    * Using method GetCardList. Documentation https://oplata.tinkoff.ru/develop/api/autopayments/getcardlist-description/
    * @param string $CardId      CardId you may get from method GetCardList
    * @param string $CustomerKey Name of existing customer (owner of card)
    */
    public function RemoveCard($CardId, $CustomerKey) {
      $params = [
        'CustomerKey' => $CustomerKey,
        'CardId' => $CardId,
      ];

      if( $this->sendRequest($this->url_removecard, $params) ){
        return $this->response_messages;
      }

      return false;
    }

    private function checkSlashOnUrlEnd($url) {
      if ( $url[strlen($url) - 1] !== '/'){
        $url .= '/';
      }
      return $url;
    }

    private function sendRequest($path,  array $args) {
      $args['TerminalKey'] = $this->terminal_key;
      $args['Token']       = $this->generateToken($args);
      $args = json_encode($args);

      if($curl = curl_init()) {
        curl_setopt($curl, CURLOPT_URL, $path);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $this->response = $response;
        $json = json_decode($response);

        if($json) {
          if ( $this->errorsFound() ) {
            return false;
          }
          else {
            $this->response_messages   = $this->MessageResponse();

            return true;
          }
        }

        $this->error .= "Can't create connection to: $path | with args: $args";
        return false;
      }

      else {
        $this->error .= "CURL init filed: $path | with args: $args";
        return false;
      }
    }

    private function initURL() {
      $this->tinkoff_url = $this->checkSlashOnUrlEnd($this->tinkoff_url);
      $this->url_init = $this->tinkoff_url . 'Init/';
      $this->url_charge = $this->tinkoff_url . 'Charge/';
      $this->url_addcustomer = $this->tinkoff_url . 'AddCustomer/';
      $this->url_getcustomer = $this->tinkoff_url . 'GetCustomer/';
      $this->url_removecustomer = $this->tinkoff_url . 'RemoveCustomer/';
      $this->url_getcardlist = $this->tinkoff_url . 'GetCardList/';
      $this->url_removecard = $this->tinkoff_url . 'RemoveCard/';
    }

    private function generateToken(array $args) {
      $token = '';
      $args['Password']    = $this->password_key;
      $args['TerminalKey'] = $this->terminal_key;
      ksort($args);

      foreach ($args as $arg) {
        if (!is_array($arg)) {
          $token .= $arg;
        }
      }

      return hash('sha256', $token);
    }

    private function errorsFound():bool {
      $response = json_decode($this->response, true);

      if (isset($response['ErrorCode'])) {
        $error_code = (int) $response['ErrorCode'];
      } else {
        $error_code = 0;
      }

      if (isset($response['Message'])) {
        $error_msg = $response['Message'];
      } else {
        $error_msg = 'Unknown error.';
      }

      if (isset($response['Details'])) {
        $error_message = $response['Details'];
      } else {
        $error_message = 'Unknown error.';
      }

      if($error_code !== 0){
        $this->error = 'Error code: '. $error_code .
        ' | Msg: ' . $error_msg .
        ' | Message: ' . $error_message;
        return true;
      }
      return false;
    }
    /**
     * Right now it's almost not work as it should be. Gonna fix later
     */
    private function MessageResponse() {
      $response = json_decode($this->response, true);

      $data = [
        'Success' => @$response['Success'],
        'CardId' => @$response['CardId'],
        'Pan' => @$response['Pan'], // card number
        'Status' => @$response['Status'],
        'RebillId' => @$response['RebillId'],
        'CardType' => @$response['CardType'],
        'ExpDate' => @$response['ExpDate'],
        'CustomerKey' => @$response['CustomerKey'],
        'PaymentURL' => @$response['PaymentURL'],
        'PaymentId' => @$response['PaymentId'],
      ];
      //return array_filter($data);
      return $response;
    }
  }
