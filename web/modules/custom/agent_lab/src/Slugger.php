<?php

declare(strict_types=1);

namespace Drupal\agent_lab;

/**
 * Service for converting text into URL-friendly slugs.
 */
class Slugger {

  /**
   * Converts text to a URL-friendly slug.
   *
   * Converts to lowercase, replaces Scandinavian characters, spaces and
   * special characters with hyphens, and removes leading/trailing hyphens.
   * Consecutive special characters produce only one hyphen.
   * Empty or special-character-only strings return 'n-a'.
   *
   * @param string $text
   *   The text to slugify.
   *
   * @return string
   *   The slugified text.
   */
  public function slugify(string $text): string {
    // Convert to lowercase.
    $slug = mb_strtolower($text, 'UTF-8');

    // Replace Scandinavian characters.
    $slug = str_replace(['ä', 'ö', 'å'], ['a', 'o', 'a'], $slug);

    // Replace spaces and special characters with hyphens.
    // Keep only alphanumeric characters and hyphens.
    $slug = preg_replace('/[^a-z0-9\-]/u', '-', $slug);

    // Collapse consecutive hyphens into a single hyphen.
    $slug = preg_replace('/-+/', '-', $slug);

    // Remove leading and trailing hyphens.
    $slug = trim($slug, '-');

    // Return 'n-a' if result is empty.
    if (empty($slug)) {
      return 'n-a';
    }

    return $slug;
  }

}
