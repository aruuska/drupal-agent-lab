<?php

declare(strict_types=1);

namespace Drupal\Tests\agent_lab\Unit;

use Drupal\agent_lab\Slugger;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for the Slugger service.
 *
 * @group agent_lab
 */
class SluggerTest extends UnitTestCase {

  /**
   * The Slugger instance to test.
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
   * Tests that slugify method exists and is callable.
   */
  public function testSlugifyMethodExists(): void {
    $this->assertTrue(method_exists($this->slugger, 'slugify'));
  }

  /**
   * Tests basic slugification with lowercase conversion.
   */
  public function testLowercaseConversion(): void {
    $result = $this->slugger->slugify('HELLO WORLD');
    $this->assertEquals('hello-world', $result);
  }

  /**
   * Tests replacement of spaces with hyphens.
   */
  public function testSpaceReplacement(): void {
    $result = $this->slugger->slugify('hello world');
    $this->assertEquals('hello-world', $result);
  }

  /**
   * Tests that no hyphens appear at start or end.
   */
  public function testNoLeadingOrTrailingHyphens(): void {
    $result = $this->slugger->slugify('  hello world  ');
    $this->assertStringNotStartsWith('-', $result);
    $this->assertStringNotEndsWith('-', $result);
  }

  /**
   * Tests Scandinavian character conversion: ä -> a.
   */
  public function testScandinavianAWithUmlaut(): void {
    $result = $this->slugger->slugify('ä');
    $this->assertEquals('a', $result);
  }

  /**
   * Tests Scandinavian character conversion: ö -> o.
   */
  public function testScandinavianOWithUmlaut(): void {
    $result = $this->slugger->slugify('ö');
    $this->assertEquals('o', $result);
  }

  /**
   * Tests Scandinavian character conversion: å -> a.
   */
  public function testScandinavianAWithRing(): void {
    $result = $this->slugger->slugify('å');
    $this->assertEquals('a', $result);
  }

  /**
   * Tests the full Scandinavian example from requirements.
   */
  public function testScandinavianExample(): void {
    $result = $this->slugger->slugify('Hyvää Yötä!');
    $this->assertEquals('hyvaa-yota', $result);
  }

  /**
   * Tests that special characters are removed.
   */
  public function testSpecialCharacterRemoval(): void {
    $result = $this->slugger->slugify('hello@world#test!');
    $this->assertEquals('hello-world-test', $result);
  }

  /**
   * Tests that consecutive special characters produce only one hyphen.
   */
  public function testConsecutiveSpecialCharacters(): void {
    $result = $this->slugger->slugify('a -- b');
    $this->assertEquals('a-b', $result);
  }

  /**
   * Tests that multiple consecutive special characters are handled correctly.
   */
  public function testMultipleConsecutiveSpecialCharacters(): void {
    $result = $this->slugger->slugify('hello---world');
    $this->assertEquals('hello-world', $result);
  }

  /**
   * Tests mixed special characters.
   */
  public function testMixedSpecialCharacters(): void {
    $result = $this->slugger->slugify('hello@#$%world');
    $this->assertEquals('hello-world', $result);
  }

  /**
   * Tests empty string returns 'n-a'.
   */
  public function testEmptyStringReturnsNA(): void {
    $result = $this->slugger->slugify('');
    $this->assertEquals('n-a', $result);
  }

  /**
   * Tests whitespace-only string returns 'n-a'.
   */
  public function testWhitespaceOnlyReturnsNA(): void {
    $result = $this->slugger->slugify('   ');
    $this->assertEquals('n-a', $result);
  }

  /**
   * Tests string with only special characters returns 'n-a'.
   */
  public function testOnlySpecialCharactersReturnsNA(): void {
    $result = $this->slugger->slugify('!@#$%^&*()');
    $this->assertEquals('n-a', $result);
  }

  /**
   * Tests string with only hyphens returns 'n-a'.
   */
  public function testOnlyHyphensReturnsNA(): void {
    $result = $this->slugger->slugify('---');
    $this->assertEquals('n-a', $result);
  }

  /**
   * Tests normal slug with numbers.
   */
  public function testNumbersPreserved(): void {
    $result = $this->slugger->slugify('Article 2024');
    $this->assertEquals('article-2024', $result);
  }

  /**
   * Tests that single word is returned as lowercase.
   */
  public function testSingleWord(): void {
    $result = $this->slugger->slugify('Hello');
    $this->assertEquals('hello', $result);
  }

  /**
   * Tests multiple spaces between words.
   */
  public function testMultipleSpacesBetweenWords(): void {
    $result = $this->slugger->slugify('hello    world');
    $this->assertEquals('hello-world', $result);
  }

  /**
   * Tests tabs and newlines are handled like spaces.
   */
  public function testWhitespaceVariations(): void {
    $result = $this->slugger->slugify("hello\tworld\ntest");
    $this->assertStringNotContainsString("\t", $result);
    $this->assertStringNotContainsString("\n", $result);
  }

  /**
   * Tests hyphen in the middle of text is handled correctly.
   */
  public function testExistingHyphenHandling(): void {
    $result = $this->slugger->slugify('hello-world');
    $this->assertEquals('hello-world', $result);
  }

  /**
   * Tests multiple hyphens in a row are collapsed.
   */
  public function testMultipleHyphensCollapsed(): void {
    $result = $this->slugger->slugify('hello-----world');
    $this->assertEquals('hello-world', $result);
  }

  /**
   * Data provider for slugify tests with edge cases.
   *
   * @return array
   *   Array of test cases with input and expected output.
   */
  public static function slugifyDataProvider(): array {
    return [
      'lowercase conversion' => ['HELLO WORLD', 'hello-world'],
      'space to hyphen' => ['hello world', 'hello-world'],
      'scandinavian ä' => ['ä', 'a'],
      'scandinavian ö' => ['ö', 'o'],
      'scandinavian å' => ['å', 'a'],
      'scandinavian uppercase' => ['ÄÖÅ', 'aoa'],
      'scandinavian example' => ['Hyvää Yötä!', 'hyvaa-yota'],
      'special character removal' => ['hello@world#test!', 'hello-world-test'],
      'consecutive special chars' => ['a -- b', 'a-b'],
      'empty string' => ['', 'n-a'],
      'whitespace only' => ['   ', 'n-a'],
      'special chars only' => ['!@#$%^&*()', 'n-a'],
      'numbers preserved' => ['Article 2024', 'article-2024'],
      'single word' => ['Hello', 'hello'],
      'multiple spaces' => ['hello    world', 'hello-world'],
      'existing hyphen' => ['hello-world', 'hello-world'],
      'multiple hyphens' => ['hello-----world', 'hello-world'],
    ];
  }

  /**
   * Tests slugify with data provider for edge cases.
   *
   * @dataProvider slugifyDataProvider
   */
  public function testSlugifyWithDataProvider(string $input, string $expected): void {
    $result = $this->slugger->slugify($input);
    $this->assertEquals($expected, $result, "Failed for input: '$input'");
  }

}
