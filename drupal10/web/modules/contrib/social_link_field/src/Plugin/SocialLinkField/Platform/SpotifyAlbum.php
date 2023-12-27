<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'spotify album' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "spotifyalbum",
 *   name = @Translation("Spotify Album"),
 *   icon = "fa-spotify",
 *   iconSquare = "spotify-square",
 *   urlPrefix = "https://open.spotify.com/album/",
 * )
 */
class SpotifyAlbum extends PlatformBase {}
