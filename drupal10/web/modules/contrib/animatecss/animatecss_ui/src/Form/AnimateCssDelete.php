<?php

namespace Drupal\animatecss_ui\Form;

use Drupal\animatecss_ui\AnimateCssManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a form to remove CSS selector.
 *
 * @internal
 */
class AnimateCssDelete extends ConfirmFormBase {

  /**
   * The Animate selector.
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
   * Constructs a new animateDelete object.
   *
   * @param \Drupal\animatecss_ui\AnimateCssManagerInterface $animate_manager
   *   The Animate selector manager.
   */
  public function __construct(AnimateCssManagerInterface $animate_manager) {
    $this->animateManager = $animate_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('animatecss.animate_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animatecss_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove %selector from animate selectors?', ['%selector' => $this->animate['selector']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $animate
   *   The Animate record ID to remove.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $animate = '') {
    if (!$this->animate = $this->animateManager->findById($animate)) {
      throw new NotFoundHttpException();
    }
    $form['animate_id'] = [
      '#type'  => 'value',
      '#value' => $animate,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $animate_id = $form_state->getValue('animate_id');
    $this->animateManager->removeAnimate($animate_id);
    $this->logger('user')
      ->notice('Deleted %selector', ['%selector' => $this->animate['selector']]);
    $this->messenger()
      ->addStatus($this->t('The Animate selector %selector was deleted.', ['%selector' => $this->animate['selector']]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('animatecss.admin');
  }

}
