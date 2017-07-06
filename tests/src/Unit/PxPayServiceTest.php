<?php

namespace Drupal\Tests\commerce_dps\Unit;

use Drupal\commerce_dps\PaymentExpress\PxPayService;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\commerce_dps\PaymentExpress\PxPayService
 *
 * @group commerce_dps
 */
class PxPayServiceTest extends UnitTestCase {

  protected $paymentExpressMock;

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

    $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->container = new ContainerBuilder();

    $this->container->set('logger.factory', $this->loggerMock);

    $this->pxPayService = new PxPayService($this->loggerMock);
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
