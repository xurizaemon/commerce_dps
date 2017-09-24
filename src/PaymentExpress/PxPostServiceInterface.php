<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Drupal\commerce_payment\Entity\PaymentInterface;

/**
 * Provides a handler for IPN requests from PayPal.
 */
interface PxPostServiceInterface {

  /**
   * Prepare xml request data to PxPay.
   */
  public function preparePxPostXmlTransaction(array $form, PaymentInterface $payment);

}
