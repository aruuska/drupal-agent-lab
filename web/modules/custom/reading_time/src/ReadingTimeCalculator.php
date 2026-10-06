<?php

declare(strict_types=1);

namespace Drupal\reading_time;

/**
 * Service for calculating estimated reading time.
 */
class ReadingTimeCalculator {

  /**
   * Reading speed in words per minute.
   */
  private const WORDS_PER_MINUTE = 200;

  /**
   * Minimum reading time in minutes.
   */
  private const MINIMUM_MINUTES = 1;

  /**
   * Calculate reading time in minutes for the given text.
   *
   * @param string $text
   *   The text to analyze.
   *
   * @return int
   *   The estimated reading time in minutes, minimum 1.
   */
  public function minutes(string $text): int {
    // Strip HTML tags from the text.
    $cleanText = strip_tags($text);

    // Split text into words using whitespace (space, tab, newline).
    // Filter out empty strings from consecutive whitespace.
    $words = array_filter(
      preg_split('/[\s]+/', $cleanText),
      static fn(string $word): bool => $word !== ''
    );

    // Count the number of words.
    $wordCount = count($words);

    // If no words, return minimum reading time.
    if ($wordCount === 0) {
      return self::MINIMUM_MINUTES;
    }

    // Calculate reading time: round up the result.
    $minutes = (int) ceil($wordCount / self::WORDS_PER_MINUTE);

    // Ensure minimum of 1 minute.
    return max($minutes, self::MINIMUM_MINUTES);
  }

}
