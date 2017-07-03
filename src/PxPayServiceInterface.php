<?php

namespace Drupal\commerce_dps;

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
   * Get pxPay gateway instance.
   */
  public function getGateway();

  /**
   * Set pxPay credentials.
   */
  public function setCredentials();

  /**
   * Set pxPay configuration property.
   */
  public function setConfiguration(array $configuration);

  /**
   * Set pxPay configuration property.
   */
  public function getConfiguration($key = NULL);

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

  /**
   * Get pxPay userID.
   */
  public function getUserId();

  /**
   * Get pxPay key.
   */
  public function getKey();

  /**
   * Get merchant reference.
   */
  public function getReference();

}
