<?php

namespace Drupal\commerce_dps\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_dps\PaymentExpress\CommercePxPay;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Paypal Express Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "dps_pxpay",
 *   label = @Translation("Payment Express (PxPay)"),
 *   display_label = @Translation("PxPay"),
 *   payment_method_types = {"credit_card"},
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_dps\PluginForm\OffSiteRedirect\PxPayForm",
 *   },
 *   credit_card_types = {
 *     "amex", "discover", "mastercard", "visa",
 *   },
 * )
 */
class PxPay extends CommercePxPay {

  /**
   * PxPay Service.
   *
   * @var \Drupal\commerce_dps\PaymentExpress\PxPayServiceInterface
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    PaymentTypeManager $payment_type_manager,
    PaymentMethodTypeManager $payment_method_type_manager
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $payment_type_manager,
      $payment_method_type_manager
    );

    $this->pxPayService = \Drupal::service('commerce_dps.pxpay_service');

    $this->gateway = $this->pxPayService->getGateway();

    $this->pxPayService->setConfiguration($configuration);

    $this->pxPayService->setCredentials();

  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge([
      'pxpay_user_id' => '',
      'pxpay_key' => '',
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

}
