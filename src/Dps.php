<?php

namespace Drupal\commerce_dps;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides Base DPS class.
 */
abstract class Dps extends OffsitePaymentGatewayBase implements DpsInterface {

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && $response->isSuccessful()) {
      $order = $request->attributes->all()['commerce_order'];
      $this->capturePayment($order, $response);
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

    if (!$response->isRedirect() && $response->isSuccessful()) {
      $this->capturePayment($order, $response);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment(OrderInterface $order, ResponseInterface $response) {

    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');

    $payment = $payment_storage->create([
      'state' => ucfirst(strtolower($response->getMessage())),
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'test' => $this->getMode() == 'test',
      'remote_id' => $response->getTransactionId(),
      'remote_state' => $response->getMessage(),
      'authorized' => \Drupal::time()->getRequestTime(),
    ]);

    $payment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge([
      'pxpay_user_id' => '',
      'pxpay_key' => '',
      'pxpay_cancel_url' => '/cart',
      'pxpay_ref_prefix' => 'Website Order',
      'pxpay_integration_method' => 'redirect',
      'pxpay_iframe_attributes' => 'width="100%" height="750" frameborder="0"',
    ], parent::defaultConfiguration());
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type')
    );
  }

}
