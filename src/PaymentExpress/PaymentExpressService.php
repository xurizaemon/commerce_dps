<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Drupal\Core\Extension\ModuleHandlerInterface;
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
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandle;

  /**
   * Constructs a new PaymentGatewayBase object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(LoggerInterface $logger, ModuleHandlerInterface $module_handler) {
    $this->logger = $logger;
    $this->moduleHandle = $module_handler;
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
    return $this->getConfiguration('px_user');
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return $this->getConfiguration('px_key');
  }

  /**
   * {@inheritdoc}
   */
  public function getReference() {
    return $this->getConfiguration('px_ref_prefix');
  }

  /**
   * {@inheritdoc}
   */
  public function isValidateCurrency($code) {

    $currencies = [
      'CAD', 'CHF', 'DKK', 'EUR', 'FRF', 'GBP', 'HKD', 'JPY',
      'NZD', 'SGD', 'THB', 'USD', 'ZAR', 'AUD', 'WST', 'VUV',
      'TOP', 'SBD', 'PGK', 'MYR', 'KWD', 'FJD',
    ];

    return in_array($code, $currencies);
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

  /**
   * {@inheritdoc}
   */
  public function getModuleHandler() {
    return $this->moduleHandle;
  }

}
