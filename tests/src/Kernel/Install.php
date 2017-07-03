<?php

namespace Drupal\Tests\commerce_dps\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Install the module.
 *
 * @group commerce_dps
 */
class Install extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['commerce_dps'];

  /**
   * Tests that an Email Log entity is created on Sendgrid event.
   */
  public function testModulesCanBeInstalled() {

    $module = \Drupal::moduleHandler()->moduleExists('commerce_dps');

    $this->assertTrue($module);
  }

}
