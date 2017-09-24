<?php

namespace Drupal\commerce_dps\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_dps\PaymentExpress\CommercePxPost;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the On-site payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "dps_pxpost",
 *   label = @Translation("Payment Express (PxPost)"),
 *   display_label = @Translation("PxPost"),
 *   forms = {
 *     "add-payment-method" = "Drupal\commerce_dps\PluginForm\OnSite\PxPostForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "discover", "mastercard", "visa",
 *   },
 * )
 */
class PxPost extends CommercePxPost {

  /**
   * Fail Proof Result Notification.
   *
   * @var \Drupal\commerce_dps\PaymentExpress\PxPostServiceInterface
   */
  protected $pxPostService;

  /**
   * PxPay gateway.
   *
   * @var \Omnipay\PaymentExpress\PxPayGateway
   */
  protected $gateway;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    PaymentTypeManager $payment_type_manager,
    PaymentMethodTypeManager $payment_method_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager);

    $this->pxPostService = \Drupal::service('commerce_dps.pxpost_service');

    $this->gateway = $this->pxPostService->getGateway();

    $this->pxPostService->setConfiguration($configuration);

    $this->pxPostService->setCredentials();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge([
      'px_user' => '',
      'px_key' => '',
      'px_ref_prefix' => 'Website Order',
    ], parent::defaultConfiguration());
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $key = 'px_user';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('PxPost Username'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'px_key';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('PxPost Password Key'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
    ];

    $key = 'px_ref_prefix';
    $form[$key] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant Reference Prefix'),
      '#default_value' => $this->configuration[$key],
      '#required' => TRUE,
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
        if (preg_match("/^px_(.*)$/i", $key)) {
          $this->configuration[$key] = $value;
        }
      }

    }
  }

}
