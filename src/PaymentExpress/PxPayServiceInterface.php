<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Drupal\commerce_payment\Entity\PaymentInterface;

/**
 * Provides a handler for IPN requests from PayPal.
 */
interface PxPayServiceInterface {

  /**
   * Prepare xml request data to PxPay.
   */
  public function preparePxPayXmlTransaction(array $form, PaymentInterface $payment);

  /**
   * Set iframe urls for xml request data to PxPay.
   */
  public function setIframeUrls(PaymentInterface $payment);

  /**
   * Is integration redirect?
   */
  public function isRedirectMethod();

  /**
   * Is integration iframe?
   */
  public function isIframeMethod();

  /**
   * Get iframe attributes.
   */
  public function getIframeAttributes();

}
