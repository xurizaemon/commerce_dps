<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Omnipay\Omnipay;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PxPayService.
 *
 * @package Drupal\commerce_dps
 */
class PxPayService extends PaymentExpressService implements PxPayServiceInterface {

  /**
   * PxPay gateway.
   *
   * @var \Omnipay\PaymentExpress\PxPayGateway
   */
  public $gateway;

  /**
   * Constructs a new PxPay Service.
   */
  public function __construct(LoggerInterface $logger) {
    parent::__construct($logger);
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

}
