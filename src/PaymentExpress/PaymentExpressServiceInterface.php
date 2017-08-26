<?php

namespace Drupal\commerce_dps\PaymentExpress;

/**
 * Provides a handler for IPN requests from PayPal.
 */
interface PaymentExpressServiceInterface {

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

  /**
   * Is this a valid currency.
   */
  public function isValidateCurrency($code);

  /**
   * Get the module handler.
   */
  public function getModuleHandler();

}
