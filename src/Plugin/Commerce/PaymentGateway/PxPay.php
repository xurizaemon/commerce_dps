<?php

namespace Drupal\commerce_dps\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_dps\PxPayServiceInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the Paypal Express Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "dps_pxpay",
 *   label = @Translation("Payment Express (PxPay)"),
 *   display_label = @Translation("PxPay"),
 *   payment_method_types = {"credit_card"},
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_dps\PluginForm\OffSiteRedirect\PxPayOffSiteForm",
 *   },
 *   credit_card_types = {
 *     "amex", "discover", "mastercard", "visa",
 *   },
 * )
 */
class PxPay extends OffsitePaymentGatewayBase implements PxPayInterface {

  /**
   * The price rounder.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  protected $rounder;

  /**
   * Fail Proof Result Notification.
   *
   * @var \Drupal\commerce_dps\PxPayServiceInterface
   */
  protected $pxPayService;

  /**
   * PxPay gateway.
   *
   * @var \Omnipay\PaymentExpress\PxPayGateway
   */
  protected $gateway;

  /**
   * Constructs a new PaymentGatewayBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_payment\PaymentTypeManager $payment_type_manager
   *   The payment type manager.
   * @param \Drupal\commerce_payment\PaymentMethodTypeManager $payment_method_type_manager
   *   The payment method type manager.
   * @param \Drupal\commerce_dps\PxPayServiceInterface $pxPayService
   *   PxPay Service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    PaymentTypeManager $payment_type_manager,
    PaymentMethodTypeManager $payment_method_type_manager,
    PxPayServiceInterface $pxPayService
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $payment_type_manager,
      $payment_method_type_manager
    );

    $this->pxPayService = $pxPayService;

    $this->gateway = $pxPayService->getGateway();

    $this->pxPayService->setConfiguration($configuration);

    $this->pxPayService->setCredentials();
  }

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {
    // Still todo.
  }

  /**
   * {@inheritdoc}
   */
  public function onCancel(OrderInterface $order, Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && !$response->isSuccessful()) {
      $this->createTransaction($order, $response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {

    /** @var \Omnipay\Common\Message\AbstractResponse $response */
    $response = $this->gateway->completePurchase([])->send();

    if (!$response->isRedirect() && $response->isSuccessful()) {
      $this->createTransaction($order, $response);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction(OrderInterface $order, ResponseInterface $response) {

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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);

    $key = 'pxpay_user_id';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('PxPay UserId'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'pxpay_key';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('PxPay Key'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'pxpay_cancel_url';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cancel Payment Url'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'pxpay_ref_prefix';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant Reference Prefix'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'pxpay_integration_method';
    $form[$key] = [
      '#type' => 'radios',
      '#title' => $this->t('Integration method'),
      '#options' => [
        'redirect' => $this->t('Redirect'),
        'iframe' => $this->t('Iframe – Embedded Hosted Payment Page'),
      ],
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'pxpay_iframe_attributes';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('Iframe attributes'),
      '#default_value' => $this->configuration[$key],
      '#states' => [
        'visible' => [
          ':input[name="configuration[dps_pxpay][pxpay_integration_method]"]' => ['value' => 'iframe'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);

      foreach ($values as $key => $value) {
        if (preg_match("/^pxpay_(.*)$/i", $key)) {
          $this->configuration[$key] = $value;
        }
      }

    }
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
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('commerce_dps.pxpay_service')
    );
  }

}
