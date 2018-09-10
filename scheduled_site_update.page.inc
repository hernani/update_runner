<?php

/**
 * @file
 * Contains scheduled_site_update.page.inc.
 *
 * Page callback for Scheduled site update entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Scheduled site update templates.
 *
 * Default template: scheduled_site_update.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_scheduled_site_update(array &$variables) {
  // Fetch ScheduledSiteUpdate Entity Object.
  $scheduled_site_update = $variables['elements']['#scheduled_site_update'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
