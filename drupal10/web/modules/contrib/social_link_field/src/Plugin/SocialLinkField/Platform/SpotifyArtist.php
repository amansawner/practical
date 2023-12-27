<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'spotify artist' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "spotifyartist",
 *   name = @Translation("Spotify Artist"),
 *   icon = "fa-spotify",
 *   iconSquare = "spotify-square",
 *   urlPrefix = "https://open.spotify.com/artist/",
 * )
 */
class SpotifyArtist extends PlatformBase {}
