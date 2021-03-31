<?php

namespace Drupal\Tests\commerce_dps\Unit;

use Drupal\commerce_dps\PaymentExpress\PaymentExpressService;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_dps\PaymentExpress\PaymentExpressService
 *
 * @group commerce_dps
 */
class PaymentExpressServiceTest extends UnitTestCase {

  /**
   * PxPay gateway.
   *
   * @var \Drupal\commerce_dps\PaymentExpress\PaymentExpressService
   */
  protected $paymentExpressService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $loggerMock = $this->createMock('Psr\Log\LoggerInterface');
    $moduleHandlerMock = $this->createMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $this->container = new ContainerBuilder();
    $this->container->set('logger.factory', $loggerMock);
    $this->paymentExpressService = new PaymentExpressService($loggerMock, $moduleHandlerMock);
  }

  /**
   * @covers ::getGateway
   */
  public function testGetGateway() {
    $this->paymentExpressService->gateway = TRUE;
    $this->assertTrue($this->paymentExpressService->getGateway());
  }

  /**
   * @covers ::getUserId
   * @dataProvider configurationProvider
   */
  public function testGetUserId($data) {
    $this->paymentExpressService->setConfiguration($data);
    $this->assertEquals('foo', $this->paymentExpressService->getUserId());
  }

  /**
   * @covers ::getKey
   * @dataProvider configurationProvider
   */
  public function testGetKey($data) {
    $this->paymentExpressService->setConfiguration($data);
    $this->assertEquals('bar', $this->paymentExpressService->getKey());
  }

  /**
   * @covers ::getReference
   * @dataProvider configurationProvider
   */
  public function testGetReference($data) {
    $this->paymentExpressService->setConfiguration($data);
    $this->assertEquals('baz', $this->paymentExpressService->getReference());
  }

  /**
   * @covers ::isValidateCurrency
   */
  public function testIsValidateCurrency() {
    $this->assertTrue($this->paymentExpressService->isValidateCurrency('NZD'));
    $this->assertTrue($this->paymentExpressService->isValidateCurrency('USD'));
    $this->assertFALSE($this->paymentExpressService->isValidateCurrency('foo'));
  }

  /**
   * @covers ::getConfiguration
   * @dataProvider configurationProvider
   */
  public function testGetConfiguration($data) {
    $this->paymentExpressService->setConfiguration($data);
    $this->assertEquals($data, $this->paymentExpressService->getConfiguration());
    $this->assertEquals($data['foo'], $this->paymentExpressService->getConfiguration('foo'));
  }

  /**
   * @covers ::setConfiguration
   */
  public function testSetConfiguration() {
    $data = ['foo'];
    $this->paymentExpressService->setConfiguration(['foo']);
    $this->assertEquals($data, $this->paymentExpressService->configuration);
  }

  /**
   * Test configuration data.
   */
  public function configurationProvider() {
    return [
      [
        [
          'px_user' => 'foo',
          'px_key' => 'bar',
          'px_ref_prefix' => 'baz',
          'foo' => 'bar',
        ],
      ],
    ];
  }

}
