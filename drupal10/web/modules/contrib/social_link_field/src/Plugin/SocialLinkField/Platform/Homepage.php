<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'homepage' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "homepage",
 *   name = @Translation("Homepage"),
 *   icon = "fa-home",
 *   iconSquare = "fa-home-square",
 *   urlPrefix = "https://www.",
 * )
 */
class Homepage extends PlatformBase {}
