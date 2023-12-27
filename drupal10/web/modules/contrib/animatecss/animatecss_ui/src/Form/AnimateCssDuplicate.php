<?php

namespace Drupal\animatecss_ui\Form;

use Drupal\animatecss_ui\AnimateCssManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to duplicate animate.
 *
 * @internal
 */
class AnimateCssDuplicate extends FormBase {

  /**
   * The animate id.
   *
   * @var int
   */
  protected $animate;

  /**
   * The Animate selector manager.
   *
   * @var \Drupal\animatecss_ui\AnimateCssManagerInterface
   */
  protected $animateManager;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new animateDuplicate object.
   *
   * @param \Drupal\animatecss_ui\AnimateCssManagerInterface $animate_manager
   *   The Animate selector manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(AnimateCssManagerInterface $animate_manager, TimeInterface $time) {
    $this->animateManager = $animate_manager;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('animatecss.animate_manager'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animatecss_duplicate_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int $animate
   *   The animate record ID to remove.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $animate = 0) {
    $form['animate_id'] = [
      '#type'  => 'value',
      '#value' => $animate,
    ];

    // New selector to duplicate effect.
    $form['selector'] = [
      '#title'         => $this->t('Selector'),
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#size'          => 64,
      '#maxlength'     => 255,
      '#default_value' => '',
      '#description'   => $this->t('Here, you can use HTML tag, class with dot(.) and ID with hash(#) prefix. Be sure your selector has plain text content. e.g. ".page-title" or ".block-title".'),
      '#placeholder'   => $this->t('Enter valid selector'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#button_type' => 'primary',
      '#value'       => $this->t('Duplicate'),
    ];

    return $form;
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
   * Form submission handler for the 'duplicate' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   A reference to a keyed array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $aid      = $values['animate_id'];
    $selector = trim($values['selector']);
    $label    = ucfirst(trim(preg_replace("/[^a-zA-Z0-9]+/", " ", $selector)));
    $status   = 1;

    $animate = $this->animateManager->findById($aid);
    $comment = $animate['comment'];
    $options = $animate['options'];

    // The Unix timestamp when the animate was most recently saved.
    $changed = $this->time->getCurrentTime();

    // Save animate.
    $new_aid = $this->animateManager->addAnimate(0, $selector, $label, $comment, $changed, $status, $options);
    $this->messenger()
      ->addStatus($this->t('The selector %selector has been duplicated.', ['%selector' => $selector]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    // Redirect to duplicated animate edit form.
    $form_state->setRedirect('animatecss.edit', ['animate' => $new_aid]);
  }

}
