<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Psr\Log\LoggerInterface;

/**
 * Class Payment Express Service.
 *
 * @package Drupal\commerce_dps
 */
class PaymentExpressService implements PaymentExpressServiceInterface {

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  public $logger;

  /**
   * Commerce gateway configuration.
   *
   * @var array
   */
  public $configuration;

  /**
   * PxPay gateway.
   *
   * @var \Omnipay\PaymentExpress\PxPayGateway
   */
  public $gateway;

  /**
   * Constructs a new PaymentGatewayBase object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel.
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getGateway() {
    return $this->gateway;
  }

  /**
   * {@inheritdoc}
   */
  public function setCredentials() {
    $this->gateway->setUsername($this->getUserId());
    $this->gateway->setPassword($this->getKey());
  }

  /**
   * {@inheritdoc}
   */
  public function getUserId() {
    return $this->getConfiguration('pxpay_user_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return $this->getConfiguration('pxpay_key');
  }

  /**
   * {@inheritdoc}
   */
  public function getReference() {
    return $this->getConfiguration('pxpay_ref_prefix');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration($key = NULL) {

    if (array_key_exists($key, $this->configuration)) {
      return $this->configuration[$key];
    };

    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

}
