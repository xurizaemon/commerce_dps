<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Drupal\Core\Url;
use Omnipay\Omnipay;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PxPayService.
 *
 * @package Drupal\commerce_dps
 */
class PxPostService extends PaymentExpressService implements PxPostServiceInterface {

  /**
   * PxPay gateway.
   *
   * @var \Omnipay\PaymentExpress\PxPostGateway
   */
  public $gateway;

  /**
   * Constructs a new PxPay Service.
   */
  public function __construct(LoggerInterface $logger) {
    parent::__construct($logger);
    $this->gateway = Omnipay::create("\\Omnipay\\PaymentExpress\\PxPostGateway");
  }

  /**
   * Prepare xml request data to PxPay.
   */
  public function preparePxPostXmlTransaction(array $form, PaymentInterface $payment) {

    $this->gateway->setCurrency($payment->getAmount()->getCurrencyCode());

    $this->gateway->setParameter('returnUrl', $form['#return_url']);

    $this->gateway->setParameter('cancelUrl', $form['#cancel_url']);

    $this->gateway->setParameter('amount', $payment->getAmount()->getNumber());

    $this->gateway->setParameter('description', $this->getReference() . ' #' . $payment->getOrderId());

  }

}
