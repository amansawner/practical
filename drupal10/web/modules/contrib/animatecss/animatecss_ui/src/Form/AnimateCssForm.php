<?php

namespace Drupal\animatecss_ui\Form;

use Drupal\animatecss_ui\AnimateCssManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Animatecss add and edit animate form.
 *
 * @internal
 */
class AnimateCssForm extends FormBase {

  /**
   * An array to store the variables.
   *
   * @var array
   */
  protected $variables = [];

  /**
   * Animate manager.
   *
   * @var \Drupal\animatecss_ui\AnimateCssManagerInterface
   */
  protected $animateManager;

  /**
   * A config object for the animate settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new Animate object.
   *
   * @param \Drupal\animatecss_ui\AnimateCssManagerInterface $animate_manager
   *   The Animate selector manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(AnimateCssManagerInterface $animate_manager, ConfigFactoryInterface $config_factory, TimeInterface $time) {
    $this->animateManager = $animate_manager;
    $this->config = $config_factory->get('animatecss.settings');
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('animatecss.animate_manager'),
      $container->get('config.factory'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animatecss_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int $animate
   *   (optional) Animate id to be passed on to
   *   \Drupal::formBuilder()->getForm() for use as the default value of the
   *   Animate ID form data.
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $animate = 0) {
    // Attach AnimateCSS form library.
    $form['#attached']['library'][] = 'animatecss_ui/animate-form';

    // Prepare AnimateCSS form default values.
    $aid      = $animate;
    $animate  = $this->animateManager->findById($animate) ?? [];
    $selector = $animate['selector'] ?? '';
    $label    = $animate['label'] ?? '';
    $comment  = $animate['comment'] ?? '';
    $status   = $animate['status'] ?? TRUE;
    $options  = [];

    // Handle the case when $animate is not an array or option is not set.
    if (is_array($animate) && isset($animate['options'])) {
      $options = unserialize($animate['options'], ['allowed_classes' => FALSE]) ?? '';
    }

    // Store animate id.
    $form['animate_id'] = [
      '#type'  => 'value',
      '#value' => $aid,
    ];

    // Store variables.
    $form['variables'] = [
      '#type'  => 'value',
      '#value' => $this->variables,
    ];

    // Load the AnimateCSS configuration settings.
    $config = $this->config;

    // The default selector to use when detecting multiple texts to animate.
    $form['selector'] = [
      '#title'         => $this->t('Selector'),
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#size'          => 64,
      '#maxlength'     => 256,
      '#default_value' => $selector,
      '#description'   => $this->t('Enter a valid element or a css selector.'),
    ];

    // The label of this selector.
    $form['label'] = [
      '#title'         => $this->t('Label'),
      '#type'          => 'textfield',
      '#required'      => FALSE,
      '#size'          => 64,
      '#maxlength'     => 64,
      '#default_value' => $label ?? '',
      '#description'   => $this->t('The label for this animate selector like <em>About block title</em>.'),
    ];

    // AnimateCSS utilities,
    // Animate.css comes packed with a few utility classes to simplify its use.
    $form['options'] = [
      '#title' => $this->t('Animate options'),
      '#type'  => 'details',
      '#open'  => TRUE,
    ];

    // The animation to use.
    $form['options']['animation'] = [
      '#title'         => $this->t('Animation'),
      '#type'          => 'select',
      '#options'       => animatecss_animation_options(),
      '#default_value' => $options['animation'] ?? $config->get('options.animation'),
      '#description'   => $this->t('Select the animation name you want to use for CSS selectors globally.'),
      '#attributes'    => ['class' => ['animate__animation']],
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
      '#default_value' => $options['delay'] ?? $config->get('options.delay'),
    ];
    $form['options']['delay_wrapper']['time'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Delay time'),
      '#default_value' => $options['time'] ?? $config->get('options.time'),
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
      '#default_value' => $options['speed'] ?? $config->get('options.speed'),
    ];
    $form['options']['duration_wrapper']['duration'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Duration'),
      '#default_value' => $options['duration'] ?? $config->get('options.duration'),
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
      '#default_value' => $options['repeat'] ?? $config->get('options.repeat'),
      '#description'   => $this->t('You can control the iteration count of the animation.'),
    ];

    $form['region'] = [
      '#type'       => 'container',
      '#attributes' => [
        'id'    => 'animatecss_sidebar',
        'class' => ['animatecss-secondary-region'],
      ],
    ];

    // Animate.css preview.
    $form['region']['preview'] = [
      '#type'   => 'details',
      '#title'  => $this->t('Animate preview'),
      '#open'   => TRUE,
    ];

    // Animate.css animation preview and replay.
    $form['region']['preview']['sample'] = [
      '#type'   => 'markup',
      '#markup' => '<div class="animate__preview"><p class="animate__sample">Animate CSS</p></div>',
    ];
    $form['region']['preview']['replay'] = [
      '#value'      => $this->t('Replay'),
      '#type'       => 'button',
      '#attributes' => ['class' => ['animate__replay']],
    ];

    $scroll_options = animatecss_scroll_options($options);
    if (count($scroll_options)) {
      foreach ($scroll_options as $library => $info) {
        // The scroll library options.
        $form['region'][$library . '_options'] = [
          '#title' => $this->t('@library options', ['@library' => $info['name']]),
          '#type'  => 'details',
          '#open'  => TRUE,
        ];

        // Enable library to Animate On Scroll.
        $form['region'][$library . '_options'][$library] = [
          '#type'          => 'checkbox',
          '#title'         => $this->t('Enable @library', ['@library' => $info['name']]),
          '#description'   => $this->t('@description', ['@description' => $info['description']]),
          '#default_value' => $options[$library]['enable'] ?? FALSE,
          '#attributes'    => ['class' => ['animate__scroll']],
        ];

        // The library setting options.
        if (isset($info['fields']) && count($info['fields'])) {
          $form['region'][$library . '_options']['setting'] = [
            '#type' => 'container',
            '#states' => [
              'disabled' => [
                ':input[name="' . $library . '"]' => ['checked' => FALSE],
              ],
              'visible' => [
                ':input[name="' . $library . '"]' => ['checked' => TRUE],
              ],
            ],
          ];
          foreach ($info['fields'] as $field => $data) {
            $form['region'][$library . '_options']['setting'][$field] = $data;
          }
        }
      }
    }

    // Sidebar close button.
    $close_sidebar_translation = $this->t('Close sidebar panel');
    $form['region']['sidebar_close'] = [
      '#markup' => '<a href="#close-sidebar" class="toggle-sidebar__close trigger" role="button" title="' . $close_sidebar_translation . '"><span class="visually-hidden">' . $close_sidebar_translation . '</span></a>',
    ];

    // The comment for describe animate settings and usage in website.
    $form['comment'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Comment'),
      '#default_value' => $comment ?? '',
      '#description'   => $this->t('Describe this animate settings and usage in your website.'),
      '#rows'          => 2,
      '#weight'        => 96,
    ];

    $form['sticky'] = [
      '#type'       => 'container',
      '#attributes' => ['class' => ['gin-sticky']],
    ];

    // Enabled status for this animate.
    $form['sticky']['status'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enabled'),
      '#default_value' => $status ?? FALSE,
      '#weight'        => 99,
    ];

    // Add sidebar toggle.
    $hide_panel = $this->t('Hide sidebar panel');
    $form['sticky']['sidebar_toggle'] = [
      '#markup' => '<a href="#toggle-sidebar" class="toggle-sidebar__trigger trigger" role="button" title="' . $hide_panel . '" aria-controls="animatecss_sidebar"><span class="visually-hidden">' . $hide_panel . '</span></a>',
      '#weight' => '999',
    ];
    $form['sidebar_overlay'] = [
      '#markup' => '<div class="toggle-sidebar__overlay trigger"></div>',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#value'       => $this->t('Save'),
      '#button_type' => 'primary',
      '#submit'      => [[$this, 'submitForm']],
    ];

    if ($aid != 0) {
      // Add a 'Remove' button for animate form.
      $form['actions']['delete'] = [
        '#type'       => 'link',
        '#title'      => $this->t('Delete'),
        '#url'        => Url::fromRoute('animatecss.delete', ['animate' => $aid]),
        '#attributes' => [
          'class' => [
            'action-link',
            'action-link--danger',
            'action-link--icon-trash',
          ],
        ],
      ];

      // Redirect to list for submit handler on edit form.
      $form['actions']['submit']['#submit'] = ['::submitForm', '::overview'];
    }
    else {
      // Add a 'Save and go to list' button for add form.
      $form['actions']['overview'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Save and go to list'),
        '#submit' => array_merge($form['actions']['submit']['#submit'], ['::overview']),
        '#weight' => 20,
      ];
    }

    return $form;
  }

  /**
   * Submit handler for removing animate.
   *
   * @param array[] $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function remove(&$form, FormStateInterface $form_state) {
    $aid = $form_state->getValue('animate_id');
    $form_state->setRedirect('animatecss.delete', ['animate' => $aid]);
  }

  /**
   * Form submission handler for the 'overview' action.
   *
   * @param array[] $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function overview(array $form, FormStateInterface $form_state): void {
    $form_state->setRedirect('animatecss.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $aid      = $form_state->getValue('animate_id');
    $is_new   = $aid == 0;
    $selector = trim($form_state->getValue('selector'));

    if ($is_new) {
      if ($this->animateManager->isAnimate($selector)) {
        $form_state->setErrorByName('selector', $this->t('This selector is already exists.'));
      }
    }
    else {
      if ($this->animateManager->findById($aid)) {
        $animate = $this->animateManager->findById($aid);

        if ($selector != $animate['selector'] && $this->animateManager->isAnimate($selector)) {
          $form_state->setErrorByName('selector', $this->t('This selector is already added.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $aid      = $values['animate_id'];
    $label    = trim($values['label']);
    $selector = trim($values['selector']);
    $comment  = trim($values['comment']);
    $status   = $values['status'];

    // Provide a label from selector if was empty.
    if (empty($label)) {
      $label = ucfirst(trim(preg_replace("/[^a-zA-Z0-9]+/", " ", $selector)));
    }

    // Set variables from main animate settings.
    $variables['animation'] = $values['animation'];
    $variables['delay']     = $values['delay'];
    $variables['time']      = $values['time'];
    $variables['speed']     = $values['speed'];
    $variables['duration']  = $values['duration'];
    $variables['repeat']    = $values['repeat'];

    // Get variables from other module.
    if (isset($values['variables']) && count($values['variables'])) {
      $variables = $variables + $values['variables'];
    }
    if (count($this->variables)) {
      $variables = array_merge($variables, $this->variables);
    }

    // Serialize options variables.
    $options = serialize($variables);

    // The Unix timestamp when the animate was most recently saved.
    $changed = $this->time->getCurrentTime();

    // Save animate.
    $this->animateManager->addAnimate($aid, $selector, $label, $comment, $changed, $status, $options);
    $this->messenger()
      ->addStatus($this->t('The selector %selector has been added.', ['%selector' => $selector]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();
  }

}
