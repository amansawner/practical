# Social Link Field

Provides a social link field type. The module has possible to customize form
widget and form formatter.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/social_link_field).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/social_link_field).


## Requirements

This module requires no modules outside of Drupal core.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

- Configure user permissions in Administration » People » Permissions:
    - Configure social link field type
      Allows or denies access to so Social Link Field Settings page.

- Customize the menu settings in Administration » Configuration »
  Web services » Social Link Field Settings.
    - Attach external Font Awesome library.
      Attach external Font Awesome (FA) library if you do not attach FA in your
      theme.


## Usage

- In entity type manages fields create a new field and select Social Links.
- Enter allowed number of values.
- Set default field values. In limited number of values, you can forbid to
  change social networks in entity create/edit form and can forbid to change
  items order.
- Set settings to form widget.
- Choose formatter and set it settings. There are 2 formatters:
    1) FontAwesome icons (Common/Square).
    2) Network name.
- Create entity.


## Customization

- To override the default FontAwesome icons just override in CSS.
- To add your custom social network, create your custom module and in path
  src/Plugin/SocialLinkField/Platform create empty php class with annotation,
  like this:

  ```
  /**
    * Provides 'PLATFORM NAME' platform.
    *
    * @SocialLinkFieldPlatform(
    *   id = "PLATFORM_ID",
    *   name = @Translation("PLATFORM NAME"),
    *   icon = "FONT_AWESOME_ICON_CLASS",
    *   iconSquare = "FONT_AWESOME_SQUARE_ICON_CLASS",
    *   urlPrefix = "PLATFORM_URL_PREFIX",
    * )
    */
    class CLASS_NAME extends PlatformBase {}
  ```


## Maintainers

- [Nick Dickinson-Wilde (NickDickinsonWilde)](https://www.drupal.org/u/nickdickinsonwilde)
- [Adrian Cid Almaguer (adriancid)](https://www.drupal.org/u/adriancid)
- [Roman Hryshkanych (romixua)](https://www.drupal.org/u/romixua)
- [Oleh Vehera (voleger)](https://www.drupal.org/u/voleger)

## Supporting organizations

- [EPAM Systems](https://www.drupal.org/epam-systems)
- [Drupiter](https://www.drupal.org/drupiter)
