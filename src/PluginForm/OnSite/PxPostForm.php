<?php

namespace Drupal\commerce_dps\PluginForm\OnSite;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\commerce_payment\PluginForm\PaymentMethodAddForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PxPostForm.
 *
 * @package Drupal\commerce_dps\PluginForm\Onsite
 */
class PxPostForm extends PaymentMethodAddForm implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  protected function buildCreditCardForm(array $element, FormStateInterface $form_state) {

    $element = parent::buildCreditCardForm($element, $form_state);

    return $element;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * This is a factory method that returns a new instance of this class. The
   * factory should pass any needed dependencies into the constructor of this
   * class, but not the container itself. Every call to this method must return
   * a new instance of this class; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_dps.pxpay_service')
    );
  }

}
