<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'facebook event' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "facebookevent",
 *   name = @Translation("Facebook Event"),
 *   icon = "fa-facebook",
 *   iconSquare = "fa-facebook-square",
 *   urlPrefix = "https://www.facebook.com/event/",
 * )
 */
class FacebookEvent extends PlatformBase {}
