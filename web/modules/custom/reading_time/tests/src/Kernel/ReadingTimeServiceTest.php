<?php

declare(strict_types=1);

namespace Drupal\Tests\reading_time\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\reading_time\ReadingTimeCalculator;

/**
 * Kernel tests for the reading_time service integration.
 *
 * @group reading_time
 */
class ReadingTimeServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['reading_time'];

  /**
   * Test that the reading_time.calculator service is available.
   */
  public function testServiceIsAvailable(): void {
    $this->assertTrue(
      $this->container->has('reading_time.calculator'),
      'The reading_time.calculator service should be registered in the container.'
    );
  }

  /**
   * Test that the service returns a ReadingTimeCalculator instance.
   */
  public function testServiceReturnsCorrectClass(): void {
    $service = $this->container->get('reading_time.calculator');
    $this->assertInstanceOf(
      ReadingTimeCalculator::class,
      $service,
      'The reading_time.calculator service should return an instance of ReadingTimeCalculator.'
    );
  }

  /**
   * Test that the service is usable for calculations.
   */
  public function testServiceCalculatesReadingTime(): void {
    $service = $this->container->get('reading_time.calculator');
    $result = $service->minutes('word');
    $this->assertIsInt($result);
    $this->assertGreaterThanOrEqual(1, $result);
  }

  /**
   * Test that multiple retrievals return the same instance (singleton).
   */
  public function testServiceIsSingleton(): void {
    $service1 = $this->container->get('reading_time.calculator');
    $service2 = $this->container->get('reading_time.calculator');
    $this->assertSame($service1, $service2);
  }

}
