<?php

namespace Drupal\animatecss_ui;

use Drupal\animatecss_ui\Form\AnimateCssFilter;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Displays Animate CSS selector.
 *
 * @internal
 */
class AnimateCssAdmin extends FormBase {

  /**
   * Animate manager.
   *
   * @var \Drupal\animatecss_ui\AnimateCssManagerInterface
   */
  protected $animateManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a new Animate object.
   *
   * @param \Drupal\animatecss_ui\AnimateCssManagerInterface $animate_manager
   *   The Animate selector manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   */
  public function __construct(AnimateCssManagerInterface $animate_manager, DateFormatterInterface $date_formatter, FormBuilderInterface $form_builder, Request $current_request) {
    $this->animateManager = $animate_manager;
    $this->dateFormatter  = $date_formatter;
    $this->formBuilder    = $form_builder;
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('animatecss.animate_manager'),
      $container->get('date.formatter'),
      $container->get('form_builder'),
      $container->get('request_stack')->getCurrentRequest(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animate_admin_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $animate
   *   (optional) CSS Selector to be added to Animate.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $animate = '') {
    // Attach AnimateCSS overview admin library.
    $form['#attached']['library'][] = 'animatecss_ui/animate-list';

    $search = $this->currentRequest->query->get('search');
    $status = $this->currentRequest->query->get('status') ?? NULL;

    /** @var \Drupal\animatecss_ui\Form\AnimateCssFilter $form */
    $form['animatecss_admin_filter_form'] = $this->formBuilder->getForm(AnimateCssFilter::class, $search, $status);
    $form['#attributes']['class'][] = 'animatecss-filter';
    $form['#attributes']['class'][] = 'views-exposed-form';

    $header = [
      [
        'data'  => $this->t('Selector'),
        'field' => 'a.aid',
      ],
      [
        'data'  => $this->t('Label'),
        'field' => 'a.label',
      ],
      [
        'data'  => $this->t('Status'),
        'field' => 'a.status',
      ],
      [
        'data'  => $this->t('Updated'),
        'field' => 'a.changed',
        'sort'  => 'desc',
      ],
      $this->t('Operations'),
    ];

    $rows = [];
    $result = $this->animateManager->findAll($header, $search, $status);
    foreach ($result as $animate) {
      $row = [];
      $row[] = $animate->selector;
      $row[] = $animate->label;
      $row[] = $animate->status ? $this->t('Enabled') : $this->t('Disabled');
      $row[] = $this->dateFormatter->format($animate->changed, 'short');
      $links = [];
      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url'   => Url::fromRoute('animatecss.edit', ['animate' => $animate->aid]),
      ];
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url'   => Url::fromRoute('animatecss.delete', ['animate' => $animate->aid]),
      ];
      $links['duplicate'] = [
        'title' => $this->t('Duplicate'),
        'url'   => Url::fromRoute('animatecss.duplicate', ['animate' => $animate->aid]),
      ];
      $row[] = [
        'data' => [
          '#type'  => 'operations',
          '#links' => $links,
        ],
      ];
      $rows[] = $row;
    }

    $form['animatecss_admin_table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $rows,
      '#empty'  => $this->t('No animate CSS selector available. <a href=":link">Add animate</a> .', [
        ':link' => Url::fromRoute('animatecss.add')
          ->toString(),
      ]),
    ];

    $form['pager'] = ['#type' => 'pager'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Add operations to animate CSS selector list
  }

}
