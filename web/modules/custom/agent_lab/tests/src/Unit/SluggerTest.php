<?php

declare(strict_types=1);

namespace Drupal\Tests\agent_lab\Unit;

use Drupal\agent_lab\Slugger;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the Slugger service.
 */
#[Group('agent_lab')]
class SluggerTest extends UnitTestCase {

  /**
   * The Slugger instance.
   *
   * @var \Drupal\agent_lab\Slugger
   */
  protected Slugger $slugger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->slugger = new Slugger();
  }

  /**
   * Tests basic slug conversion.
   *
   * @param string $input
   *   The input text.
   * @param string $expected
   *   The expected slug output.
   */
  #[DataProvider('provideSlugifyTestCases')]
  public function testSlugify(string $input, string $expected): void {
    $result = $this->slugger->slugify($input);
    $this->assertSame($expected, $result);
  }

  /**
   * Test cases for slugify method.
   *
   * @return array<int, array<string>>
   *   Array of test cases with input and expected output.
   */
  public static function provideSlugifyTestCases(): array {
    return [
      // Basic lowercase conversion
      'uppercase to lowercase' => ['HELLO', 'hello'],
      'mixed case to lowercase' => ['Hello World', 'hello-world'],

      // Whitespace handling
      'single space to dash' => ['hello world', 'hello-world'],
      'multiple spaces to single dash' => ['hello  world', 'hello-world'],
      'leading space removal' => [' hello', 'hello'],
      'trailing space removal' => ['hello ', 'hello'],
      'leading and trailing spaces removal' => [' hello world ', 'hello-world'],

      // Scandinavian characters
      'ä to a' => ['äpple', 'apple'],
      'ö to o' => ['söda', 'soda'],
      'å to a' => ['åker', 'aker'],
      'multiple scandinavian chars' => ['Hyvää Yötä!', 'hyvaa-yota'],
      'uppercase scandinavian' => ['ÄÖÅ', 'aoa'],
      'leading and trailing dashes' => ['--hello--', 'hello'],
      'leading and trailing special chars' => ['!hello?', 'hello'],

      // Special characters handling
      'dash in text' => ['hello-world', 'hello-world'],
      'multiple dashes' => ['hello--world', 'hello-world'],
      'multiple special chars' => ['hello!!!world', 'hello-world'],
      'dot and comma' => ['hello.world,test', 'hello-world-test'],

      // Complex cases
      'a -- b' => ['a -- b', 'a-b'],
      'multiple consecutive special chars' => ['hello---world', 'hello-world'],
      'mixed special chars and spaces' => ['hello - world', 'hello-world'],
      'underscore handling' => ['hello_world', 'hello-world'],

      // Empty and special input
      'empty string' => ['', 'n-a'],
      'only spaces' => ['   ', 'n-a'],
      'only special chars' => ['!!!', 'n-a'],
      'only dashes' => ['---', 'n-a'],
      'only mixed special chars' => ['!@#$%', 'n-a'],

      // Edge cases
      'single character' => ['a', 'a'],
      'single special char' => ['!', 'n-a'],
      'accented single char' => ['ä', 'a'],
      'number' => ['123', '123'],
      'text with numbers' => ['test123', 'test123'],
    ];
  }

}
