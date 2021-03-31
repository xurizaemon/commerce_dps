<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
use Drupal\commerce_payment_example\Plugin\Commerce\PaymentGateway\OnsiteInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;

/**
 * Provides Base DPS class.
 */
abstract class CommercePxPost extends OnsitePaymentGatewayBase implements OnsiteInterface {

  /**
   * {@inheritdoc}
   */
  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    // @todo Implement createPaymentMethod() method.
  }

  /**
   * {@inheritdoc}
   */
  public function createPayment(PaymentInterface $payment, $capture = TRUE) {
    // @todo Implement createPayment() method.
  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment(PaymentInterface $payment, Price $amount = NULL) {
    // @todo Implement capturePayment() method.
  }

  /**
   * {@inheritdoc}
   */
  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {
    // @todo Implement refundPayment() method.
  }

  /**
   * {@inheritdoc}
   */
  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    // @todo Implement deletePaymentMethod() method.
  }

  /**
   * {@inheritdoc}
   */
  public function voidPayment(PaymentInterface $payment) {
    // @todo Implement voidPayment() method.
  }

}
