<?php

namespace Drupal\commerce_dps\PaymentExpress;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides Base DPS class.
 */
abstract class CommercePxPay extends OffsitePaymentGatewayBase implements CommercePxPayInterface {

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && $response->isSuccessful()) {
      $order = $request->attributes->all()['commerce_order'];
      $this->capturePayment($order, $response);
      $order->state = 'completed';
      $order->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function onCancel(OrderInterface $order, Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && !$response->isSuccessful()) {
      $this->capturePayment($order, $response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && $response->isSuccessful() && $order->state->value !== 'completed') {
      $this->capturePayment($order, $response);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment(OrderInterface $order, ResponseInterface $response) {

    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');

    $data = [
      'state' => ucfirst(strtolower($response->getMessage())),
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'test' => $this->getMode() == 'test',
      'remote_id' => $response->getTransactionId(),
      'remote_state' => $response->getMessage(),
      'authorized' => \Drupal::time()->getRequestTime(),
    ];

    $payment = $payment_storage->create($data);

    $payment->save();
  }

}
