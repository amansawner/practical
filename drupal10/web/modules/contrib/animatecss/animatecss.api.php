<?php

/**
 * @file
 * Hooks for the AnimateCSS module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide animatecss animation names.
 *
 * By implementing hook_animatecss_animation_names(), a module can add
 * animation name to the list and extent css animation with new custom animate.
 *
 * @param array $animation_name
 *   List of new animation names in array.
 *
 * @return array
 *   A render array that can be added into Animate CSS animation names.
 */
function hook_animatecss_animation_names($animation_name = '') {
  $animation_names = [];

  return $animation_names;
}

/**
 * Provide animatecss scroll options.
 *
 * By implementing hook_animatecss_scroll_options(), a module can integrate
 * scroll reveal library to the list and extent animate scroll options.
 *
 * @param array $option
 *   The scroll library option data and fields in array.
 *
 * @return array
 *   A render array that can be added into Animate CSS scroll options.
 */
function hook_animatecss_scroll_options($option) {
  $libraries_info['library_machine_name'] = [
    'name' => 'Library Name',
    'description' => 'Reveal CSS animation as you scroll down a page.',
    'fields' => [
      'library_machine_name_offset' => [
        '#type'          => 'number',
        '#min'           => 0,
        '#title'         => t('Offset'),
        '#default_value' => $options['library_machine_name']['offset'] ?? '',
        '#field_suffix'  => 'px',
      ],
      'mobile' => [
        '#type'          => 'checkbox',
        '#title'         => t('Mobile'),
        '#description'   => t("Trigger animations on mobile devices."),
        '#default_value' => $options['library_machine_name']['mobile'] ?? '',
      ],
      'live' => [
        '#type'          => 'checkbox',
        '#title'         => t('Live'),
        '#description'   => t("Act on asynchronously loaded content."),
        '#default_value' => $options['library_machine_name']['live'] ?? '',
      ],
      'optional_container' => [
        '#type'          => 'checkbox',
        '#title'         => t('Scroll container'),
        '#description'   => t('Optional scroll container selector, otherwise use window.'),
        '#default_value' => $options['library_machine_name']['optionalContainer'] ?? '',
      ],
      'scroll_container' => [
        '#type'          => 'textfield',
        '#title_display' => 'invisible',
        '#title'         => t('Container selector'),
        '#description'   => t('Scroll container selector.'),
        '#default_value' => $options['library_machine_name']['scrollContainer'] ?? '',
        '#states'        => [
          'visible' => [
            ':input[name="optional_container"]' => ['checked' => TRUE],
          ],
        ],
      ],
    ],
  ];
  return $libraries_info;
}

/**
 * @} End of "addtogroup hooks".
 */
