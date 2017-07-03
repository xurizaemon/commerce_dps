<?php

namespace Drupal\commerce_dps;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Omnipay\Omnipay;
use Drupal\commerce_payment\Entity\PaymentInterface;

/**
 * Class PxPayService.
 *
 * @package Drupal\commerce_dps
 */
class PxPayService implements PxPayServiceInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->gateway = Omnipay::create("\\Omnipay\\PaymentExpress\\PxPayGateway");
  }

  /**
   * Prepare xml request data to PxPay.
   */
  public function preparePxPayXmlTransaction(array $form, PaymentInterface $payment) {

    $this->gateway->setCurrency($payment->getAmount()->getCurrencyCode());

    $this->gateway->setParameter('returnUrl', $form['#return_url']);

    $this->gateway->setParameter('cancelUrl', $form['#cancel_url']);

    $this->gateway->setParameter('amount', $payment->getAmount()->getNumber());

    $this->gateway->setParameter('description', $this->getReference() . ' #' . $payment->getOrderId());
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
  public function isRedirectMethod() {
    return $this->getConfiguration('pxpay_integration_method') === 'redirect';
  }

  /**
   * {@inheritdoc}
   */
  public function isIframeMethod() {
    return $this->getConfiguration('pxpay_integration_method') === 'iframe';
  }

  /**
   * {@inheritdoc}
   */
  public function getIframeAttributes() {
    return $this->getConfiguration('pxpay_iframe_attributes');
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
