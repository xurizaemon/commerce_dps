<?php

namespace Drupal\Tests\commerce_dps\Unit;

use Drupal\commerce_dps\PaymentExpress\PxPayService;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_dps\PaymentExpress\PxPayService
 *
 * @group commerce_dps
 */
class PxPayServiceTest extends UnitTestCase {

  /**
   * PxPay gateway.
   *
   * @var \Drupal\commerce_dps\PaymentExpress\PxPayService
   */
  protected $pxPayService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $loggerMock = $this->createMock('Psr\Log\LoggerInterface');
    $moduleHandlerMock = $this->createMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $this->container = new ContainerBuilder();
    $this->container->set('logger.factory', $loggerMock);
    $this->pxPayService = new PxPayService($loggerMock, $moduleHandlerMock);
  }

  /**
   * @covers ::setIframeUrls
   * @dataProvider configurationProvider
   */
  public function setIframeUrls($data) {
    $this->pxPayService->setConfiguration($data);
    $this->assertEquals('foo', $this->pxPayService->getUserId());
  }

  /**
   * @covers ::isRedirectMethod
   * @dataProvider configurationProvider
   */
  public function testIsRedirectMethod($data) {
    $this->pxPayService->setConfiguration($data);
    $this->assertTrue($this->pxPayService->isRedirectMethod());
  }

  /**
   * @covers ::isIframeMethod
   * @dataProvider configurationProvider
   */
  public function testIsIframeMethod($data) {
    $this->pxPayService->setConfiguration($data);
    $this->assertFalse($this->pxPayService->isIframeMethod());
  }

  /**
   * @covers ::getIframeAttributes
   * @dataProvider configurationProvider
   */
  public function testGetIframeAttributes($data) {
    $this->pxPayService->setConfiguration($data);
    $this->assertEquals('baz', $this->pxPayService->getIframeAttributes());
  }

  /**
   * Test configuration data.
   */
  public function configurationProvider() {
    return [
      [
        [
          'pxpay_integration_method' => 'redirect',
          'pxpay_iframe_attributes' => 'baz',
        ],
      ],
    ];
  }

}
