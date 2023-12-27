<?php

namespace Drupal\wowjs_ui\Form;

use Drupal\animatecss_ui\Form\AnimateCssSettings;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures wow settings.
 */
class WowJsSettings extends AnimateCssSettings {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Get current settings.
    $wowConfig = $this->config('wowjs.settings');

    $form['wow_settings'] = [
      '#type'  => 'details',
      '#title' => $this->t('WOW settings'),
      '#open'  => FALSE,
    ];

    // Show warning missing library and lock on cdn method.
    $method = $wowConfig->get('method');
    $method_lock_change = FALSE;
    if (!wowjs_check_installed()) {
      $method = 'cdn';
      $method_lock_change = TRUE;
      $method_warning = $this->t('You cannot set local due to the WOW.js javascript library is missing. Please <a href=":downloadUrl" rel="external" target="_blank">Download the library</a> and and extract to "/libraries/wow" directory.', [
        ':downloadUrl' => 'https://github.com/matthieua/WOW/archive/master.zip',
      ]);

      // Hide library warning message.
      $form['wow_settings']['hide'] = [
        '#type'          => 'checkbox',
        '#title'         => $this->t('Hide warning'),
        '#default_value' => $wowConfig->get('hide') ?? FALSE,
        '#description'   => $this->t("If you want to use the CDN without installing the local library, you can turn off the warning."),
      ];

      $form['wow_settings']['wow_method_warning'] = [
        '#type'   => 'item',
        '#markup' => '<div class="library-status-report">' . $method_warning . '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="hide"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    // Load method library from CDN or Locally.
    $form['wow_settings']['wow_method'] = [
      '#type'          => 'select',
      '#options'       => [
        'local' => $this->t('Local'),
        'cdn'   => $this->t('CDN'),
      ],
      '#title'         => $this->t('Add WOW.js method'),
      '#description'   => $this->t('These settings control how the WOW.js library is loaded. You can choose to load from the CDN (External source) or from the local (Internal library).'),
      '#default_value' => $method,
      '#disabled'      => $method_lock_change,
    ];

    // Production or minimized version.
    $form['wow_settings']['wow_minimized'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Development or Production version'),
      '#collapsible' => TRUE,
      '#collapsed'   => FALSE,
    ];
    $form['wow_settings']['wow_minimized']['wow_minimized_options'] = [
      '#type'          => 'radios',
      '#options'       => [
        0 => $this->t('Use non-minimized library (Development)'),
        1 => $this->t('Use minimized library (Production)'),
      ],
      '#title'         => $this->t('Choose minimized or non-minimized library.'),
      '#description'   => $this->t('These settings work both methods with locally and CDN library.'),
      '#default_value' => $wowConfig->get('minimized.options'),
    ];

    // WOW.js default options.
    $form['wow_options'] = [
      '#type'   => 'details',
      '#title'  => $this->t('WOW default options'),
      '#open'   => FALSE,
    ];

    // List of animated element css class (default is wow).
    $form['wow_options']['box_class'] = [
      '#type'          => 'textfield',
      '#size'          => 12,
      '#title'         => $this->t('WOW animated element css class'),
      '#description'   => $this->t('Enter simple classname without dot (.) for your animated elements, Default is "wow" and you can change the css class to anything you want. This class will added automatically to your target element selector to trigger with wow javascript. e.g. "classname".'),
      '#default_value' => $wowConfig->get('options.classBox'),
      '#required'      => TRUE,
    ];

    // Distance to the element when triggering the animation (default is 0).
    $form['wow_options']['offset'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Offset'),
      '#description'   => $this->t('Set the distance to the element when triggering the animation.'),
      '#default_value' => $wowConfig->get('options.offset'),
      '#field_suffix'  => 'px',
    ];

    // Trigger animations on mobile devices (default is true).
    $form['wow_options']['mobile'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Mobile'),
      '#description'   => $this->t("Trigger animations on mobile devices."),
      '#default_value' => $wowConfig->get('options.mobile'),
    ];

    // Act on asynchronously loaded content (default is true).
    $form['wow_options']['live'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Live'),
      '#description'   => $this->t("Act on asynchronously loaded content."),
      '#default_value' => $wowConfig->get('options.live'),
    ];

    // Enable scroll container selector, otherwise use window.
    $form['wow_options']['optional_container'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Scroll container'),
      '#description'   => $this->t('Optional scroll container selector, otherwise use window.'),
      '#default_value' => $wowConfig->get('options.optionalContainer'),
    ];

    // Optional scroll container selector.
    $form['wow_options']['scroll_container'] = [
      '#type'          => 'textfield',
      '#title_display' => 'invisible',
      '#title'         => $this->t('Container selector'),
      '#description'   => $this->t('Scroll container selector.'),
      '#default_value' => $wowConfig->get('options.scrollContainer'),
      '#states'        => [
        'visible' => [
          ':input[name="optional_container"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if (empty(trim($values['box_class']))) {
      $form_state->setErrorByName('box_class', $this->t('This class name cannot be empty.'));
    }
    elseif (strrpos($values['box_class'], ' ') !== FALSE) {
      $form_state->setErrorByName('box_class', $this->t('There should be no space in the class name.'));
    }
    elseif (str_starts_with(trim($values['box_class']), '.')) {
      $form_state->setErrorByName('box_class', $this->t('It is not necessary to put the dot(.) at the beginning class name.'));
    }
    elseif (str_starts_with(trim($values['box_class']), '#')) {
      $form_state->setErrorByName('box_class', $this->t('There should be no # at the beginning the class name.'));
    }
    else {
      if (preg_match('/[^a-zA-Z]/', trim($values['box_class']))) {
        $form_state->setErrorByName('box_class', $this->t('The name of the class should only use lowercase letters. Symbols, numbers and spaces are not allowed.'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the updated WOW.js settings.
    $this->config('wowjs.settings')
      ->set('hide', isset($values['hide']) && $values['hide'] !== 0 ?? FALSE)
      ->set('method', $values['wow_method'])
      ->set('minimized.options', $values['wow_minimized_options'])
      ->set('options.classBox', strtolower(trim($values['box_class'])))
      ->set('options.offset', $values['offset'])
      ->set('options.mobile', $values['mobile'])
      ->set('options.live', $values['live'])
      ->set('options.optionalContainer', $values['optional_container'])
      ->set('options.scrollContainer', $values['scroll_container'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'animatecss.settings',
      'wowjs.settings',
    ];
  }

}
