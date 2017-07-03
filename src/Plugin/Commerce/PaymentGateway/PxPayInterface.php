<?php

namespace Drupal\commerce_dps\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Provides the interface for the Express Checkout payment gateway.
 */
interface PxPayInterface {

  /**
   * Get pxPay Integration method.
   */
  public function createTransaction(OrderInterface $order, ResponseInterface $response);

}
