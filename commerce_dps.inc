<?php

/**
 * This file contains code used by multiple DPS methods.
 */

/**
 * Returns an array of possible currency codes.
 */
function commerce_dps_currencies() {
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

/**
 * Returns the transaction type string for DPS PX Post that corresponds
 * to the Drupal Commerce constant.
 *
 * @param $txn_type
 *   A Drupal Commerce transaction type constant.
 *
 * @return string
 *   'Purchase', 'Auth', 'Complete', 'Refund', or 'Validate'.
 *
 * Auth: Authorizes a transactions. Must be completed within 7 days
 * using the "Complete" TxnType.
 *
 * Complete: Completes (settles) a pre-approved Auth Transaction. The
 * DpsTxnRef value returned by the original approved Auth transaction
 * must be supplied.
 *
 * Purchase: Purchase - Funds are transferred immediately.
 *
 * Refund: Refund - Funds transferred immediately. Must be enabled as
 * a special option.
 *
 * Validate: Validation Transaction. Effects a $1.00 Auth to validate
 * card details including expiry date. Often utilised with the
 * EnableAddBillCard property set to 1 to automatically add to Billing
 * Database if the transaction is approved.
 */
function commerce_dps_txn_type($txn_type) {
  switch ($txn_type) {
    // Used to just authorize an amount on a credit card account. Must
    // be completed within 7 days.
    case COMMERCE_CREDIT_AUTH_ONLY:
      return 'Auth';

    // Used to capture funds from a prior authorization.
    case COMMERCE_CREDIT_PRIOR_AUTH_CAPTURE:
      return 'Complete';

    // Used to authorize and capture money all at once.
    case COMMERCE_CREDIT_CAPTURE_ONLY:
    // Used to just capture an amount on a credit card account.
    case COMMERCE_CREDIT_AUTH_CAPTURE:
    // Used to capture funds using a credit card reference.
    case COMMERCE_CREDIT_REFERENCE_TXN:
      return 'Purchase';

    // Used to set up a credit card reference through the payment
    // gateway.
    case COMMERCE_CREDIT_REFERENCE_SET:
      // Use with EnableAddBillCard=1
      return 'Validate';

    // Used to credit funds to a credit card account.
    case COMMERCE_CREDIT_CREDIT:
    // Used to credit funds to a reference at the payment gateway.
    case COMMERCE_CREDIT_REFERENCE_CREDIT:
      // DPS docs suggest this must be enabled as a special option,
      // not available per default.
      return 'Refund';

    // Used to void a transaction before the transaction clears.
    case COMMERCE_CREDIT_VOID:
    // Used to remove a reference from the payment gateway.
    case COMMERCE_CREDIT_REFERENCE_REMOVE:
    default:
      // No apparent support for these types in DPS.
      return NULL;
  }
}

/**
 * Returns the description of a DPS PX transaction type.
 *
 * @param $txn_type
 *   A DPS transaction type string.
 */
function commerce_dps_reverse_txn_type($txn_type) {
  switch (strtoupper($txn_type)) {
    case 'AUTH':
      return t('Authorization only');
    case 'COMPLETE':
      return t('Prior authorization capture');
    case 'PURCHASE':
      return t('Purchase');
    case 'REFUND':
      return t('Refund');
    case 'VALIDATE':
      return t('Validate only');
  }
}
