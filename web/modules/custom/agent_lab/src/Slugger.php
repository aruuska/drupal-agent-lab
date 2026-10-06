<?php

declare(strict_types=1);

namespace Drupal\agent_lab;

/**
 * Service for converting text to URL-friendly slugs.
 */
class Slugger {

  /**
   * Converts text to a URL-friendly slug.
   *
   * @param string $text
   *   The text to convert.
   *
   * @return string
   *   The slug, lowercase with spaces and special characters replaced by dashes.
   */
  public function slugify(string $text): string {
    // Handle empty or whitespace-only input
    if (empty(trim($text))) {
      return 'n-a';
    }

    // Replace Scandinavian characters
    $text = strtr($text, [
      'ä' => 'a',
      'Ä' => 'a',
      'ö' => 'o',
      'Ö' => 'o',
      'å' => 'a',
      'Å' => 'a',
    ]);

    // Convert to lowercase
    $text = mb_strtolower($text);

    // Replace underscores with spaces for consistent handling
    $text = str_replace('_', ' ', $text);

    // Replace non-alphanumeric characters (except spaces) with spaces
    $text = preg_replace('/[^a-z0-9\s-]/u', ' ', $text);

    // Replace multiple consecutive spaces or dashes with a single dash
    $text = preg_replace('/[\s\-]+/', '-', $text);

    // Remove leading and trailing dashes
    $text = trim($text, '-');

    // Handle case where the result is empty (only special chars in input)
    if (empty($text)) {
      return 'n-a';
    }

    return $text;
  }

}
