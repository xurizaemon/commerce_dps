<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Drupal\commerce_order\Entity\OrderInterface;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Dps Interface.
 */
interface CommercePxPayInterface {

  /**
   * Capture the payment.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   Order entity.
   * @param \Omnipay\Common\Message\ResponseInterface $response
   *   Omnipay response.
   */
  public function capturePayment(OrderInterface $order, ResponseInterface $response);

}
