<?php

declare(strict_types=1);

namespace Drupal\Tests\agent_lab\Kernel;

use Drupal\agent_lab\Slugger;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests that the agent_lab.slugger service is properly registered.
 */
#[Group('agent_lab')]
class SluggerServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['agent_lab'];

  /**
   * Tests that the agent_lab.slugger service exists.
   */
  public function testSluggerServiceExists(): void {
    $this->assertTrue(
      $this->container->has('agent_lab.slugger'),
      'The agent_lab.slugger service must be registered.'
    );
  }

  /**
   * Tests that the agent_lab.slugger service returns a Slugger instance.
   */
  public function testSluggerServiceReturnsSluggerInstance(): void {
    $slugger = $this->container->get('agent_lab.slugger');
    $this->assertInstanceOf(Slugger::class, $slugger);
  }

  /**
   * Tests that the slugger service is usable.
   */
  public function testSluggerServiceIsUsable(): void {
    $slugger = $this->container->get('agent_lab.slugger');
    $result = $slugger->slugify('Test Slug');
    $this->assertSame('test-slug', $result);
  }

}
