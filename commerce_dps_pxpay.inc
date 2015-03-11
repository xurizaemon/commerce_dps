<?php

/**
 * @file
 * DPS PX Pay shared functions.
 */

/**
 * Generate request; if valid, return URL to redirect to.
 */
function commerce_dps_pxpay_generate_request($transaction) {
  module_load_include('inc', 'commerce_dps_pxpost', 'commerce_dps');

  $xml = '<GenerateRequest>';
  $xml .= '  <PxPayUserId>' . htmlspecialchars($transaction['user_id']) . '</PxPayUserId>';
  $xml .= '  <PxPayKey>' . htmlspecialchars($transaction['key']) . '</PxPayKey>';
  $xml .= '  <TxnType>' . htmlspecialchars($transaction['type']) . '</TxnType>';
  $xml .= '  <CurrencyInput>' . htmlspecialchars($transaction['currency']) . '</CurrencyInput>';
  $xml .= '  <AmountInput>' . htmlspecialchars($transaction['amount']) . '</AmountInput>';
  $xml .= '  <MerchantReference>' . htmlspecialchars($transaction['reference']) . '</MerchantReference>';
  $xml .= '  <EmailAddress>' . htmlspecialchars($transaction['email']) . '</EmailAddress>';
  $xml .= '  <TxnId>' . htmlspecialchars($transaction['txn_id']) . '</TxnId>';
  $xml .= '  <UrlSuccess>' . htmlspecialchars($transaction['url_success']) . '</UrlSuccess>';
  $xml .= '  <UrlFail>' . htmlspecialchars($transaction['url_failure']) . '</UrlFail>';
  if (!empty($transaction['add_bill_card'])) {
    $xml .= '  <EnableAddBillCard>1</EnableAddBillCard>';
  }
  $xml .= '</GenerateRequest>';

  if ($transaction['log']) {
    watchdog('commerce_dps_pxpay', 'GenerateRequest: @reference, @xml', array('@reference' => $transaction['reference'], '@xml' => $xml), WATCHDOG_DEBUG);
  }

  $url = $transaction['server'];
  $params = array(
    'method' => 'POST',
    'data' => $xml,
    'headers' => array(
      'Content-type' =>  'application/xml',
      'User-Agent' => 'Drupal Commerce DPS PX Pay (+https://drupal.org/project/commerce_dps)'
    ),
  );
  $response = drupal_http_request($url, $params);

  if ($transaction['log']) {
    watchdog(
      'commerce_dps_pxpay',
      'GenerateRequest response: @reference, HTTP @code, @xml',
      array(
        '@reference' => $transaction['reference'],
        '@code' => $response->code,
        '@xml' => isset($response->data) ? $response->data : '',
      ),
      WATCHDOG_DEBUG
    );
  }

  // If response from PX Pay is ok, extract and return
  // Hosted Payment Page URI to redirect user to.
  if ($response->code == 200) {
    if ($xml = simplexml_load_string($response->data)) {
      foreach ($xml->attributes() as $attribute => $value) {
        if ($attribute == 'valid' && $value == '1') {
          return (string)$xml->URI;
        }
        else {
          watchdog('commerce_dps_pxpay',
            'GenerateRequest invalid: @reference, HTTP @code, XML: @xml',
            array(
              '@reference' => $transaction['reference'],
              '@code' => $response->code,
              '@xml' => isset($response->data) ? $response->data : '',
            ),
            WATCHDOG_ERROR
          );
        }
      }
    }
  }
  return FALSE;
}

/**
 * Generate XML for PX Pay Process Response
 *
 * @param array $transaction Details of transaction.
 *
 * @return array $data Transaction data.
 */
function commerce_dps_pxpay_process_response($transaction) {
  $xml = '<ProcessResponse>';
  $xml .= '<PxPayUserId>' . $transaction['user_id'] . '</PxPayUserId>';
  $xml .= '<PxPayKey>' . $transaction['key'] . '</PxPayKey>';
  $xml .= '<Response>' . $transaction['result'] . '</Response>';
  $xml .= '</ProcessResponse>';

  $url = variable_get('commerce_dps_pxpay_server', 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx');
  $response = drupal_http_request($url, array('method' => 'POST', 'data' => $xml));

  if ($transaction['log']) {
    watchdog('commerce_dps_pxpay', 'ProcessResponse response: HTTP @code, <pre>@xml</pre>', array('@code' => $response->code, '@xml' => $response->data), WATCHDOG_DEBUG);
  }

  // If response XML from PX Pay is good, extract and return
  // transaction data.
  if ($response->code == 200) {
    $xml = simplexml_load_string($response->data);
    // Build data array.
    foreach ($xml->attributes() as $attribute => $value) {
      if ($attribute == 'valid' && $value == '1') {
        $data = array();
        // Iterate over (flat) XML elements and gather values.
        foreach ($xml->children() as $child) {
          $data[$child->getName()] = (string) $child;
        }
        return $data;
      }
    }
  }
  return FALSE;
}

/**
 * Create a transaction and associate it with an order.
 *
 * @param  object $order   The order object being processed
 * @param  array  $payment Payment details from DPS
 * @param  string $status  The payment status to use for the transaction
 *
 * @return void
 */
function commerce_dps_pxpay_order_transaction($order, $payment, $status){
  // Create a new payment transaction for the order.
  $transaction = commerce_payment_transaction_new('commerce_dps_pxpay', $order->order_id);
  $transaction->instance_id = 'dps_pxpay';
  $transaction->remote_id = $payment['TxnId'];
  $transaction->amount = commerce_currency_decimal_to_amount($payment['AmountSettlement'], $payment['CurrencyInput']);
  $transaction->currency_code = $payment['CurrencyInput'];
  $transaction->payload[REQUEST_TIME] = $payment;

  // Set the transaction's statuses based on the payment_status.
  $transaction->remote_status = $payment['ResponseText'];
  $transaction->status = $status;

  $message = array(
    'Status: @status',
    'Detail: @statusdetail',
    'Email: @email',
    'Auth Code: @authcode.'
  );
  $transaction->message = implode('<br>', $message);
  $transaction->message_variables = array(
    '@status' => $payment['Success'],
    '@statusdetail' => $payment['ResponseText'],
    '@email' => $payment['EmailAddress'],
    '@authcode' => $payment['AuthCode'],
  );

  // Save the transaction information.
  commerce_payment_transaction_save($transaction);

  // Additionally, if we have a DpsBillingId and Commerce Card on File
  // is enabled, let's save it.
  if (module_exists('commerce_cardonfile')) {
    if (!empty($payment['DpsBillingId'])) {
      // Build a payment details array for the credit card.
      $payment_details = array(
        'cardNumber' => $payment['CardNumber'],
        'expirationDate' => substr($payment['DateExpiry'], 0, 2) . '-' . substr($payment['DateExpiry'], 2, 4),
      );
      // First look to see if we already have cards on file for the user.
      $stored_cards = commerce_cardonfile_load_multiple_by_uid($order->uid, $payment['instance_id']);
      $add_to_profile = NULL;

      // Obtain the payment method used.
      if (!empty($order->data['payment_method'])) {
        $payment_method = commerce_payment_method_instance_load($order->data['payment_method']);
      }

      if (!empty($stored_cards)) {
        // @TODO: Iterate through stored cards, remove any with
        // matching details but differing BillingId.
      }
      // Store the card details.
      $card_data = commerce_cardonfile_new();
      $card_data->uid = $order->uid;
      $card_data->payment_method = $payment_method['method_id'];
      $card_data->instance_id = $payment_method['instance_id'];
      $card_data->remote_id = $payment['DpsBillingId'];
      $card_data->card_type = !empty($card_type) ? $card_type : 'card';
      $card_data->card_name = $payment['CardHolderName'];
      $card_data->card_number = substr($payment['CardNumber'], -4, 4);
      $card_data->card_exp_month = substr($payment['DateExpiry'], 0, 2);
      $card_data->card_exp_year = '20' . substr($payment['DateExpiry'], 2, 4);
      $card_data->status = 1;

      // Save and log the creation of the new card on file.
      commerce_cardonfile_save($card_data);
    }
  }

}

/**
 * Get all the transaction remote ids for a given order number.
 *
 * @param int $order_id Unique id of Commerce Order.
 *
 * @return array Remote IDs.
 */
function commerce_dps_pxpay_get_remote_ids($order_id){
  $transaction_ids = commerce_dps_pxpay_get_transaction_ids($order_id);
  $transactions = commerce_payment_transaction_load_multiple($transaction_ids);
  $remote_ids = array();
  foreach ($transactions as $transaction) {
    $remote_ids[] = $transaction->remote_id;
  }
  return $remote_ids;
}

/**
 * Get all the transaction ids for a given order number.
 *
 * @param  int $order_id The order id the check against
 *
 * @return array This array contains all the transaction ids for the order
 */
function commerce_dps_pxpay_get_transaction_ids($order_id){
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'commerce_payment_transaction')
    ->propertyCondition('order_id', $order_id, '=');
  $transactions = $query->execute();
  $transaction_ids = array();
  if (array_key_exists('commerce_payment_transaction', $transactions)){
    foreach ($transactions['commerce_payment_transaction'] as $transaction) {
      $transaction_ids[] = $transaction->transaction_id;
    }
  }
  return $transaction_ids;
}
