<?php

namespace Drupal\animatecss_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures animate settings.
 */
class AnimateCssSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animatecss_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Attach AnimateCSS settings page library.
    $form['#attached']['library'][] = 'animatecss_ui/animate-settings';

    // Get current settings.
    $config = $this->config('animatecss.settings');

    $form['settings'] = [
      '#type'  => 'details',
      '#title' => $this->t('Animate settings'),
      '#open'  => TRUE,
    ];

    // Let module handle load Animate.css library.
    $form['settings']['load'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Load Animate.css library'),
      '#default_value' => $config->get('load'),
      '#description'   => $this->t("If enabled, this module will attempt to load the Animate.css library for your site. To prevent loading twice, leave this option disabled if you're including the assets manually or through another module or theme."),
    ];

    // Let module load Animate.compat.css library.
    $form['settings']['compat'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Use Animate.css compat version'),
      '#default_value' => $config->get('compat'),
      '#description'   => $this->t("If enabled, this module will attempt to load the Animate.compat.css library for your site. Provide support for migration from v3.x and Under without need to change your classes."),
    ];

    // Show warning missing library and lock on cdn method.
    $method = $config->get('method');
    $method_lock_change = FALSE;
    if (!animatecss_check_installed()) {
      $method = 'cdn';
      $method_lock_change = TRUE;
      $method_warning = $this->t('You cannot set local due to the Animate.css library is missing. Please <a href=":downloadUrl" rel="external" target="_blank">Download the library</a> and and extract to "/libraries/animate.css" directory.', [
        ':downloadUrl' => 'https://github.com/animate-css/animate.css/archive/main.zip',
      ]);

      // Display warning message off.
      $form['settings']['silent'] = [
        '#type'          => 'checkbox',
        '#title'         => $this->t('Turn off warning'),
        '#default_value' => $config->get('silent') ?? FALSE,
        '#description'   => $this->t("If you want to use the CDN without installing the local library, you can turn off the warning."),
      ];

      $form['settings']['method_warning'] = [
        '#type'   => 'item',
        '#markup' => '<div class="library-status-report">' . $method_warning . '</div>',
        '#states' => [
          'visible' => [
            ':input[name="load"]' => ['checked' => TRUE],
          ],
          'invisible' => [
            ':input[name="silent"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    // Load method library from CDN or Locally.
    $form['settings']['method'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Add Animate.css method'),
      '#options'       => [
        'local' => $this->t('Local'),
        'cdn'   => $this->t('CDN'),
      ],
      '#default_value' => $method,
      '#description'   => $this->t('These settings control how the Animate.css library is loaded. You can choose to load from the CDN (External source) or from the local (Internal library).'),
      '#disabled'      => $method_lock_change,
    ];

    // Production or minimized version.
    $form['settings']['minimized'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Development or Production version'),
      '#collapsible' => TRUE,
      '#collapsed'   => FALSE,
      '#states'      => [
        'invisible' => [
          'select[name="compat"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="compat"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['settings']['minimized']['minimized_options'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Choose minimized or non-minimized library.'),
      '#options'       => [
        0 => $this->t('Use non-minimized library (Development)'),
        1 => $this->t('Use minimized library (Production)'),
      ],
      '#default_value' => $config->get('minimized.options'),
      '#description'   => $this->t('These settings work with both local library and CDN methods, but this not applicable in the compat version because the compatible library is only available in minimized mode.'),
    ];

    // Load Animate.css library Per-path.
    $form['settings']['url'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Load on specific URLs'),
      '#collapsible' => TRUE,
      '#collapsed'   => TRUE,
    ];
    $form['settings']['url']['url_visibility'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Load animate.css on specific pages'),
      '#options'       => [
        0 => $this->t('All pages except those listed'),
        1 => $this->t('Only the listed pages'),
      ],
      '#default_value' => $config->get('url.visibility'),
    ];
    $form['settings']['url']['url_pages'] = [
      '#type'          => 'textarea',
      '#title'         => '<span class="element-invisible">' . $this->t('Pages') . '</span>',
      '#default_value' => _animatecss_ui_array_to_string($config->get('url.pages')),
      '#description'   => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is %admin-wildcard for every user page. %front is the front page.", [
        '%admin-wildcard' => '/admin/*',
        '%front'          => '<front>',
      ]),
    ];

    // Animate.css Utilities,
    // Comes packed with a few utility classes to simplify its use.
    $form['options'] = [
      '#type'  => 'details',
      '#title' => $this->t('Animate default options'),
      '#open'  => TRUE,
    ];

    // List of selectors to individual Animate Control with Animate.css.
    $form['options']['selectors'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Global selectors'),
      '#default_value' => _animatecss_ui_array_to_string($config->get('options.selector')),
      '#description'   => $this->t('Enter CSS selector (id/class) of your elements e.g., "#id" or ".classname". Use a new line for each selector.'),
      '#rows'          => 3,
    ];

    // The animation to use.
    $form['options']['animation'] = [
      '#title'         => $this->t('Animation'),
      '#type'          => 'select',
      '#options'       => animatecss_animation_options(),
      '#default_value' => $config->get('options.animation'),
      '#description'   => $this->t('Select the animation name you want to use for CSS selectors globally.'),
    ];

    // Animate.css provides the following delays.
    // Animate duration used as a prefix on CSS Variables.
    $form['options']['delay_wrapper'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => ['animatecss-wrapper', 'container-inline', 'form-item'],
      ],
    ];
    $form['options']['delay_wrapper']['delay'] = [
      '#title'         => $this->t('Delay'),
      '#type'          => 'select',
      '#options'       => animatecss_delay_options(),
      '#default_value' => $config->get('options.delay'),
    ];
    $form['options']['delay_wrapper']['time'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Delay time'),
      '#default_value' => $config->get('options.duration'),
      '#field_suffix'  => 'ms',
      '#attributes'    => ['class' => ['animate-delay-time']],
      '#states'        => [
        'visible' => [
          'select[name="delay"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $form['options']['delay_wrapper']['time_description'] = [
      '#type'   => 'markup',
      '#markup' => $this->t('The provided delays are from 1 to 5 seconds. You can customize them by selecting custom and set the delay time directly on the elements to delay the start of the animation.'),
      '#prefix' => '<div class="form-item__description">',
      '#suffix' => '</div>',
    ];

    // Animate speed time and duration used as a prefix on CSS Variables.
    $form['options']['duration_wrapper'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => ['animatecss-wrapper', 'container-inline', 'form-item'],
      ],
    ];
    $form['options']['duration_wrapper']['speed'] = [
      '#title'         => $this->t('Speed'),
      '#type'          => 'select',
      '#options'       => animatecss_speed_options(),
      '#default_value' => $config->get('options.speed'),
    ];
    $form['options']['duration_wrapper']['duration'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Duration'),
      '#default_value' => $config->get('options.duration'),
      '#field_suffix'  => 'ms',
      '#attributes'    => ['class' => ['animate-duration']],
      '#states'        => [
        'visible' => [
          'select[name="speed"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $form['options']['duration_wrapper']['speed_description'] = [
      '#type'   => 'markup',
      '#markup' => $this->t('You can control the speed of the animation. The medium option speed is 1 second same as a default speed. You can also set the animations duration by selecting customize option.'),
      '#prefix' => '<div class="form-item__description">',
      '#suffix' => '</div>',
    ];

    // Animate iteration count.
    $form['options']['repeat'] = [
      '#title'         => $this->t('Repeating'),
      '#type'          => 'select',
      '#options'       => animatecss_repeat_options(),
      '#default_value' => $config->get('options.repeat'),
      '#description'   => $this->t('You can control the iteration count of the animation.'),
      '#states'        => [
        'visible' => [
          ':input[name="infinite"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Animate.css preview.
    $form['preview'] = [
      '#type'   => 'details',
      '#title'  => $this->t('Animate preview'),
      '#open'   => TRUE,
    ];

    // Animate.css animation preview.
    $form['preview']['sample'] = [
      '#type'   => 'markup',
      '#markup' => '<div class="animate__preview"><p class="animate__sample">An animated element!</p></div>',
    ];

    // Replay button for preview Animate.css current configs.
    $form['preview']['replay'] = [
      '#value'      => $this->t('Replay'),
      '#type'       => 'button',
      '#attributes' => ['class' => ['animate__replay']],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Animate field verification.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the updated Animate.css settings.
    $this->config('animatecss.settings')
      ->set('load', $values['load'])
      ->set('silent', isset($values['silent']) && $values['silent'] !== 0 ?? FALSE)
      ->set('method', $values['method'])
      ->set('compat', $values['compat'])
      ->set('minimized.options', $values['minimized_options'])
      ->set('url.visibility', $values['url_visibility'])
      ->set('url.pages', _animatecss_ui_string_to_array($values['url_pages']))
      ->set('options.selector', _animatecss_ui_string_to_array($values['selectors']))
      ->set('options.animation', $values['animation'])
      ->set('options.delay', $values['delay'])
      ->set('options.time', $values['time'])
      ->set('options.speed', $values['speed'])
      ->set('options.duration', $values['duration'])
      ->set('options.repeat', $values['repeat'])
      ->save();

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'animatecss.settings',
    ];
  }

}
