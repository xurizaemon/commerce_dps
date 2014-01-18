<?php

/**
 * @file
 * DPS lib functions.
 */

/**
 * Generate request; if valid, return URL to redirect to.
 */
function commerce_dps_pxpay_generate_request($transaction) {
  // Ensure amount is dd.cc
  switch ($transaction['currency']) {
    case 'JPY':
      $transaction['amount'] = number_format($transaction['amount'], 0, '', '');
      break;
    default:
      $transaction['amount'] = number_format($transaction['amount'], 2, '.', '');
      break;
  }

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
  $xml .= '</GenerateRequest>';

  if ($transaction['log']) {
    watchdog('commerce_dps_pxpay', 'GenerateRequest: @reference, @xml', array('@reference' => $transaction['reference'], '@xml' => $xml), WATCHDOG_DEBUG);
  }

  $url = $transaction['server'];
  $params = array('method' => 'POST', 'data' => $xml);
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
          return $xml->URI;
        }
        else {
          watchdog('commerce_dps_pxpay',
            'GenerateRequest invalid: @reference, XML: @xml',
            array(
              '@reference' => $transaction['reference'],
              '@xml' => isset($response->data) ? $response->data : '',
            ),
            WATCHDOG_ERROR
          );
        }
      }
    }
  }
  drupal_set_message('Unable to communicate with DPS. Please advise the site administrator.', 'error'); 
  watchdog(
    'commerce_dps_pxpay',
    'GenerateRequest: @reference, HTTP @code, XML: @xml',
    array(
      '@reference' => $transaction['reference'],
      '@code' => $response->code,
      '@xml' => isset($response->data) ? $response->data : '',
    ),
    WATCHDOG_ERROR
  );
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
  // In testing, I'm sure I saw this work without setting
  // PxPayUserId ... weird.
  $xml = '<ProcessResponse>';
  $xml .= '<PxPayUserId>' . $transaction['user_id'] . '</PxPayUserId>';
  $xml .= '<PxPayKey>' . $transaction['key'] . '</PxPayKey>';
  $xml .= '<Response>' . $transaction['result'] . '</Response>';
  $xml .= '</ProcessResponse>';

  $url = variable_get('commerce_dps_pxpay_server', 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx');
  $response = drupal_http_request($url, array('method' => 'POST', 'data' => $xml));

  if ($transaction['log']) {
    watchdog('commerce_dps_pxpay', 'ProcessResponse response: HTTP @code, @xml', array('@code' => $response->code, '@xml' => $response->data), WATCHDOG_DEBUG);
  }

  // If response XML from PX Pay is good, extract and return transaction data.
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
      else {
        // @TODO: Send to watchdog.
      }
    }
  }
  return FALSE;
}

/**
 * Returns an array of possible currency codes.
 */
function commerce_dps_pxpay_currencies() {
  return array(
    'AUD' => 'Australian Dollar',
    'CAD' => 'Canadian Dollar',
    'EUR' => 'Euro',
    'FJD' => 'Fiji Dollar',
    'FRF' => 'French Franc',
    'HKD' => 'Hong Kong Dollar',
    'JPY' => 'Japanese Yen',
    'KWD' => 'Kuwaiti Dinar',
    'MYR' => 'Malaysian Ringgit',
    'NZD' => 'New Zealand Dollar',
    'PNG' => 'Papua New Guinea Kina',
    'ZAR' => 'South African Rand',
    'SGD' => 'Singapore Dollar',
    'SBD' => 'Solomon Islands Dollar',
    'TOP' => 'Tongan Pa\'anga',
    'WST' => 'Samoan Tala',
    'CHF' => 'Swiss Franc',
    'GBP' => 'United Kingdom Pound',
    'USD' => 'United States Dollar',
    'VUV' => 'Vanuatu Vatu',
  );
}
