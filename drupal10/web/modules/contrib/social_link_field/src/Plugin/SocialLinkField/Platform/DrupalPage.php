<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'drupalpage' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "drupalpage",
 *   name = @Translation("Drupal Page"),
 *   icon = "fa-drupal",
 *   urlPrefix = "https://www.drupal.org/",
 * )
 */
class DrupalPage extends PlatformBase {}
