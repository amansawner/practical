<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'spotify playlist' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "spotifyplaylist",
 *   name = @Translation("Spotify Playlist"),
 *   icon = "fa-spotify",
 *   iconSquare = "spotify-square",
 *   urlPrefix = "https://open.spotify.com/playlist/",
 * )
 */
class SpotifyPlaylist extends PlatformBase {}
