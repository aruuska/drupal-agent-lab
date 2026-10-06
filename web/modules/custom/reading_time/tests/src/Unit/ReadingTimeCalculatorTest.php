<?php

declare(strict_types=1);

namespace Drupal\Tests\reading_time\Unit;

use Drupal\reading_time\ReadingTimeCalculator;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for ReadingTimeCalculator service.
 *
 * @group reading_time
 */
class ReadingTimeCalculatorTest extends UnitTestCase {

  /**
   * The ReadingTimeCalculator instance.
   *
   * @var \Drupal\reading_time\ReadingTimeCalculator
   */
  protected ReadingTimeCalculator $calculator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->calculator = new ReadingTimeCalculator();
  }

  /**
   * Data provider for minute calculation tests.
   *
   * @return array<int, array<int, mixed>>
   *   Arrays of [text, expected_minutes].
   */
  public static function minuteCalculationProvider(): array {
    return [
      // Edge case: empty text should return minimum 1 minute.
      ['', 1],
      // Edge case: whitespace-only text should return minimum 1 minute.
      ['   ', 1],
      ["\t\n  ", 1],
      // Single word: 1/200 rounds up to 1 minute.
      ['word', 1],
      // 200 words: exactly 1 minute (200/200 = 1).
      [implode(' ', array_fill(0, 200, 'word')), 1],
      // 201 words: rounds up to 2 minutes (201/200 = 1.005 -> 2).
      [implode(' ', array_fill(0, 201, 'word')), 2],
      // 400 words: exactly 2 minutes (400/200 = 2).
      [implode(' ', array_fill(0, 400, 'word')), 2],
      // 401 words: rounds up to 3 minutes (401/200 = 2.005 -> 3).
      [implode(' ', array_fill(0, 401, 'word')), 3],
      // Simple HTML stripping: <p>yksi kaksi</p> is 2 words.
      ['<p>yksi kaksi</p>', 1],
      [implode('  ', array_fill(0, 200, 'word')), 1],
      [implode("\n", array_fill(0, 201, 'word')), 2],
      [implode("\t", array_fill(0, 201, 'word')), 2],
      [implode(" \t\n ", array_fill(0, 200, 'word')), 1],
      // HTML with multiple tags.
      ['<div><p>word</p></div>', 1],
      // Complex HTML stripping with multiple words.
      [sprintf('<div>%s</div>', implode(' ', array_fill(0, 200, 'word'))), 1],
      [sprintf('<div>%s</div>', implode(' ', array_fill(0, 201, 'word'))), 2],
      // Multiple types of whitespace separators.
      ['word1 word2	word3' . "\n" . 'word4', 1],
      // Consecutive whitespace: should not create empty words.
      ['word1    word2', 1],
      ['word1' . "\t\t" . 'word2', 1],
      // Mixed HTML and whitespace.
      ['<p>word1  </p>  <div>word2</div>', 1],
      // HTML entities and special characters should be stripped.
      ['<p>word1&nbsp;word2</p>', 1],
      // Nested HTML tags.
      ['<div><p>word1 word2 word3</p></div>', 1],
      // 1 word in HTML.
      ['<p>singularword</p>', 1],
      // Minimum 1 minute even with tiny text.
      ['a', 1],
      // Test with actual multi-word content.
      [sprintf('<p>%s</p>', implode(' ', array_fill(0, 199, 'word'))), 1],
      [sprintf('<p>%s</p>', implode(' ', array_fill(0, 200, 'word'))), 1],
      [sprintf('<p>%s</p>', implode(' ', array_fill(0, 201, 'word'))), 2],
    ];
  }

  /**
   * Test minute calculation with various inputs.
   *
   * @param string $text
   *   The text to analyze.
   * @param int $expected
   *   The expected reading time in minutes.
   *
   * @dataProvider minuteCalculationProvider
   */
  public function testMinutesCalculation(string $text, int $expected): void {
    $result = $this->calculator->minutes($text);
    $this->assertSame($expected, $result);
    $this->assertIsInt($result);
  }

  /**
   * Test that minutes method returns an integer.
   */
  public function testMinutesReturnsInteger(): void {
    $result = $this->calculator->minutes('word');
    $this->assertIsInt($result);
  }

  /**
   * Test that result is always at least 1 minute.
   */
  public function testMinimumResultIsOneMinute(): void {
    $testCases = ['', '  ', 'a', '<p></p>', '<div>  </div>'];
    foreach ($testCases as $text) {
      $result = $this->calculator->minutes($text);
      $this->assertGreaterThanOrEqual(1, $result, sprintf(
        'Expected at least 1 minute for text: "%s"',
        $text
      ));
    }
  }

  /**
   * Test HTML stripping behavior.
   */
  public function testHtmlStripppingBehavior(): void {
    // Basic HTML stripping.
    $htmlText = '<p>word1 word2</p>';
    $result = $this->calculator->minutes($htmlText);
    $this->assertSame(1, $result);

    // Nested HTML.
    $complexHtml = '<div><p><span>word1</span> <strong>word2</strong></p></div>';
    $result = $this->calculator->minutes($complexHtml);
    $this->assertSame(1, $result);

    // Self-closing tags.
    $selfClosing = 'word1<br/>word2<hr/>word3';
    $result = $this->calculator->minutes($selfClosing);
    $this->assertSame(1, $result);
  }

  /**
   * Test whitespace handling.
   */
  public function testWhitespaceHandling(): void {
    // Various whitespace characters as separators.
    $spacesSeparated = 'word1 word2 word3 word4';
    $tabsSeparated = 'word1	word2	word3	word4';
    $newlinesSeparated = "word1\nword2\nword3\nword4";
    $mixedWhitespace = "word1 \t\n word2";

    $spacesResult = $this->calculator->minutes($spacesSeparated);
    $tabsResult = $this->calculator->minutes($tabsSeparated);
    $newlinesResult = $this->calculator->minutes($newlinesSeparated);
    $mixedResult = $this->calculator->minutes($mixedWhitespace);

    // All should identify 4 words and return 1 minute.
    $this->assertSame(1, $spacesResult);
    $this->assertSame(1, $tabsResult);
    $this->assertSame(1, $newlinesResult);
    $this->assertSame(1, $mixedResult);
  }

  /**
   * Test edge case with exactly reading speed boundary.
   */
  public function testReadingSpeedBoundary(): void {
    // 200 words = exactly 1 minute at 200 words/min.
    $text200 = implode(' ', array_fill(0, 200, 'w'));
    $this->assertSame(1, $this->calculator->minutes($text200));

    // 201 words should round up to 2 minutes.
    $text201 = implode(' ', array_fill(0, 201, 'w'));
    $this->assertSame(2, $this->calculator->minutes($text201));

    // 400 words = exactly 2 minutes.
    $text400 = implode(' ', array_fill(0, 400, 'w'));
    $this->assertSame(2, $this->calculator->minutes($text400));

    // 401 words should round up to 3 minutes.
    $text401 = implode(' ', array_fill(0, 401, 'w'));
    $this->assertSame(3, $this->calculator->minutes($text401));
  }

}
