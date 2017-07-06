<?php

namespace Drupal\commerce_dps\PluginForm\OffSiteRedirect;

use Drupal\commerce_dps\PaymentExpress\PxPayServiceInterface;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PxPayOffSiteForm.
 *
 * @package Drupal\commerce_dps\PluginForm\OffsiteRedirect
 */
class PxPayForm extends PaymentOffsiteForm implements ContainerInjectionInterface {

  /**
   * The PxPay Service.
   *
   * @var \Drupal\commerce_dps\Plugin\Commerce\PaymentGateway\PxPay
   */
  protected $pxPayService;

  /**
   * PxPay gateway.
   *
   * @var \Omnipay\PaymentExpress\PxPayGateway
   */
  protected $gateway;

  /**
   * PxPayOffSiteForm constructor.
   */
  public function __construct(PxPayServiceInterface $pxPayService) {
    $this->pxPayService = $pxPayService;
    $this->gateway = $pxPayService->getGateway();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;

    $this->pxPayService->preparePxPayXmlTransaction($form, $payment);

    if ($this->pxPayService->getConfiguration('mode') === 'test') {
      $this->gateway->setTestMode(TRUE);
    }

    /** @var \Omnipay\PaymentExpress\Message\PxPayAuthorizeResponse $request */
    $request = $this->gateway->purchase()->send();

    if (empty($request->getRedirectUrl())) {
      $this->pxPayService->logger->error($request->getData()->ResponseText);
    }

    if ($this->pxPayService->isRedirectMethod()) {

      $form = $this->buildRedirectForm(
        $form,
        $form_state,
        $request->getRedirectUrl(),
        [],
        $request->getRedirectMethod());
    }

    if ($this->pxPayService->isIframeMethod()) {

      $form['iframe'] = [
        '#markup' => sprintf(
          "<iframe src='%s' %s></iframe>",
          $request->getRedirectUrl(),
          $this->pxPayService->getIframeAttributes()
        ),
        '#allowed_tags' => ['iframe'],
      ];

    }

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_dps.pxpay_service')
    );
  }

}
