<?php

namespace ClanMax\TinkoffAutopay;

/**
*
*/
class TinkoffAutopay extends {

  function __construct($url, $terminalkey, $passwordkey)
  {
    $this->tinkoff_url = $url;
    $this->terminal_key = $terminalkey;
    $this->password_key = $passwordkey;
  }

  public function Charge($PaymentId, $RebillId) {
    $params = [
      'PaymentId' => $PaymentId,
      'RebillId' => $RebillId,
    ];

    if( $this->sendRequest($this->url_charge, $params) ){
      return $this->payment_status;
    }

    return false;
  }

  public function AddCustomer($CustomerKey) {
    $params = [
      'CustomerKey' => $CustomerKey,
    ];

    if( $this->sendRequest($this->url_addcustomer, $params) ){
      return $this->payment_status;
    }

    return false;
  }

  public function GetCustomer($CustomerKey) {
    $params = [
      'CustomerKey' => $CustomerKey,
    ];

    if( $this->sendRequest($this->url_getcustomer, $params) ){
      return $this->payment_status;
    }

    return false;
  }

  public function RemoveCustomer($CustomerKey) {
    $params = [
      'CustomerKey' => $CustomerKey,
    ];

    if( $this->sendRequest($this->url_removecustomer, $params) ){
      return $this->payment_status;
    }

    return false;
  }

  public function GetCardList($CustomerKey) {
    $params = [
      'CustomerKey' => $CustomerKey,
    ];

    if( $this->sendRequest($this->url_getcardlist, $params) ){
      return $this->payment_status;
    }

    return false;
  }

  public function RemoveCard($CardId, $CustomerKey) {
    $params = [
      'CustomerKey' => $CustomerKey,
      'CardId' => $CardId,
    ];

    if( $this->sendRequest($this->url_removecard, $params) ){
      return $this->payment_status;
    }

    return false;
  }

  private function initURL() {
    $this->tinkoff_url = $this->checkSlashOnUrlEnd($this->tinkoff_url);
    $this->url_charge = $this->tinkoff_url . 'Charge/';
    $this->url_addcustomer = $this->tinkoff_url . 'AddCustomer/';
    $this->url_getcustomer = $this->tinkoff_url . 'GetCustomer/';
    $this->url_removecustomer = $this->tinkoff_url . 'RemoveCustomer/';
    $this->url_getcardlist = $this->tinkoff_url . 'GetCardList/';
    $this->url_removecard = $this->tinkoff_url . 'RemoveCard/';
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
            $this->responce_messages   = @$this->MessageResponse();

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
    ];
  }

}
