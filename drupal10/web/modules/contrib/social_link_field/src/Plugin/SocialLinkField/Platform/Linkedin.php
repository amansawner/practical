<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'linkedin' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "linkedin",
 *   name = @Translation("LinkedIn"),
 *   icon = "fa-linkedin-in",
 *   iconSquare = "fa-linkedin",
 *   urlPrefix = "https://www.linkedin.com/",
 * )
 */
class Linkedin extends PlatformBase {}
