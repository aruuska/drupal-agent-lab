<?php

declare(strict_types=1);

namespace Drupal\Tests\agent_lab\Kernel;

use Drupal\agent_lab\Slugger;
use Drupal\KernelTests\KernelTestBase;

/**
 * Kernel tests for agent_lab module service registration.
 *
 * @group agent_lab
 */
class SluggerServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['agent_lab'];

  /**
   * Tests that the slugger service is registered and available.
   */
  public function testSluggerServiceIsRegistered(): void {
    $service = \Drupal::service('agent_lab.slugger');
    $this->assertNotNull($service);
  }

  /**
   * Tests that the slugger service returns a Slugger instance.
   */
  public function testSluggerServiceReturnsSluggerInstance(): void {
    $service = \Drupal::service('agent_lab.slugger');
    $this->assertInstanceOf(Slugger::class, $service);
  }

  /**
   * Tests that the service is accessible via container.
   */
  public function testSluggerServiceViaContainer(): void {
    $container = \Drupal::getContainer();
    $this->assertTrue($container->has('agent_lab.slugger'));
  }

  /**
   * Tests that the service has the slugify method.
   */
  public function testSluggerServiceHasSlugifyMethod(): void {
    $service = \Drupal::service('agent_lab.slugger');
    $this->assertTrue(method_exists($service, 'slugify'));
  }

  /**
   * Tests that the service slugify method accepts a string parameter.
   */
  public function testSluggerServiceSlugifyAcceptsString(): void {
    $service = \Drupal::service('agent_lab.slugger');
    $result = $service->slugify('test');
    $this->assertIsString($result);
  }

  /**
   * Tests that slugify returns a string result.
   */
  public function testSluggerServiceSlugifyReturnsString(): void {
    $service = \Drupal::service('agent_lab.slugger');
    $result = $service->slugify('Hello World');
    $this->assertIsString($result);
    $this->assertEquals('hello-world', $result);
  }

}
