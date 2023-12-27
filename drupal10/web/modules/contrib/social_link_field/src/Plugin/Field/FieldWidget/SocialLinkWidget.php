<?php

namespace Drupal\social_link_field\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Plugin implementation of the 'open_hours' widget.
 *
 * @FieldWidget(
 *   id = "social_links",
 *   label = @Translation("Social links"),
 *   field_types = {
 *     "social_links"
 *   }
 * )
 */
class SocialLinkWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Platform of social networks.
   *
   * @var array
   */
  protected $platforms;

  /**
   * Route name.
   *
   * @var string
   */
  protected $routeName;

  /**
   * Field cardinality.
   *
   * @var int
   */
  protected $cardinality;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, $platforms_service, $route_match) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->platforms = $platforms_service->getPlatforms();

    // Limit available platforms to the field settings.
    if ($limited_platforms = $field_definition->getSetting('platforms')) {
      if ($limited_platforms = array_filter($limited_platforms)) {
        $this->platforms = array_intersect_key($this->platforms, $limited_platforms);
      }
    }

    $this->routeName = $route_match->getRouteName();
    $this->cardinality = $this
      ->fieldDefinition
      ->getFieldStorageDefinition()
      ->getCardinality();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.social_link_field.platform'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'select_social' => FALSE,
      'disable_weight' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    if ($this->cardinality > 0) {
      $element['select_social'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Possibility to select social network'),
        '#default_value' => $this->getSetting('select_social'),
      ];
    }
    $element['disable_weight'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Forbidden to change weight'),
      '#default_value' => $this->getSetting('disable_weight'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = array_merge($element['#field_parents'], [$field_name, $delta]);
    $element['#parents'] = $parents;
    $unique_element_id = Crypt::hashBase64(implode($parents));
    $field_required = $this->fieldDefinition->isRequired();
    $admin_route = !(stripos($this->routeName, 'entity.field_config') === FALSE);
    $unlimited = $this->cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED;

    // Detect if we need to show or hide social select.
    // We show social select if this setting is checked,
    // cardinality is unlimited, or we are on field config form.
    $enable_social = (
      $this->getSetting('select_social')
      || $unlimited
      || $admin_route
    ) ? TRUE : FALSE;

    // Detect default values.
    $default_vales = $this->getFormValues($enable_social, $items, $delta, $form_state);
    $social = $default_vales['social'];
    $link = $default_vales['link'];

    // Social network select.
    $element['social'] = [
      '#type' => $enable_social ? 'select' : 'hidden',
      '#title' => $this->t('Social network'),
      '#default_value' => $social,
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#data' => [
        'field_name' => $field_name,
        'delta' => $delta,
        'unique_element_id' => $unique_element_id,
      ],
      '#ajax' => [
        'event' => 'change',
        'callback' => [$this, 'updateLinkName'],
      ],
    ];
    foreach ($this->platforms as $platform) {
      $element['social']['#options'][$platform['id']]
        = $platform['name']->getUntranslatedString();
    }
    // Social link input.
    // Required when not in config, enable social is active & social newtwork
    // is set.
    $link_required = $field_required || ($unlimited && !empty($social) && !$admin_route);
    $element['link'] = [
      '#type' => 'textfield',
      '#title' => $enable_social || empty($social) ? $this->t('Profile link') : $this->platforms[$social]['name']->getUntranslatedString(),
      '#required' => $link_required,
      '#default_value' => $link,
      '#attributes' => [],
      '#field_prefix' => !empty($social) ? $this->platforms[$social]['urlPrefix'] : '',
      '#prefix' => '<div id="' . $unique_element_id . '-' . $delta . '-link-wrapper">',
      '#suffix' => '</div>',
    ];

    // Remove item button.
    if ($this->cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $element['actions'] = [
        '#type' => 'actions',
        'remove_button' => [
          '#delta' => $delta,
          '#name' => implode('_', $parents) . '_remove_button',
          '#type' => 'submit',
          '#value' => $this->t('Remove'),
          '#limit_validation_errors' => [],
          '#submit' => [[static::class, 'removeSubmit']],
          '#ajax' => [
            'callback' => [$this, 'ajaxRemove'],
            'effect' => 'fade',
            'wrapper' => $form['#wrapper_id'],
          ],
          '#weight' => 1000,
        ],
      ];
    }

    return $element;
  }

  /**
   * Ajax callback function for update link dynamically.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function updateLinkName(array $form, FormStateInterface $form_state, Request $request) {
    $response = new AjaxResponse();
    $element = $form_state->getTriggeringElement();
    $field_name = $element['#data']['field_name'];
    $delta = $element['#data']['delta'];

    // Get field element on default field settings form.
    if (isset($form['default_value']) && $form['default_value']['widget']['#field_name'] == $field_name) {
      $element_link = $form['default_value']['widget'][$delta]['link'];
    }
    elseif (isset($element['#array_parents'])
      && (in_array('subform', $element['#array_parents'], TRUE)
        || (in_array('form', $element['#array_parents'], TRUE)))) {
      $parents = $element['#array_parents'];
      array_pop($parents);
      $element_link = $this->getElementLink($form, $parents);
    }
    else {
      $element_link = $form[$field_name]['widget'][$delta]['link'];
    }
    if (!empty($element['#value'])) {
      $element_link['#field_prefix'] = $this->platforms[$element['#value']]['urlPrefix'];
    }

    $response->addCommand(new ReplaceCommand('#' . $element['#data']['unique_element_id'] . '-' . $delta . '-link-wrapper', $element_link));

    return $response;
  }

  /**
   * Ensure items order to prevent delta issues.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Original items.
   */
  protected function ensureItemsOrder(FieldItemListInterface $items) {
    $default_values = $this->fieldDefinition->getDefaultValueLiteral();
    $items_array = $items->getValue();
    $new_items = [];
    $map = [];

    // Try to locate delta from default value.
    // Working even with multiple same rrss.
    foreach ($default_values as $default_delta => $default) {
      $set = FALSE;
      foreach ($items_array as $saved_delta => $item) {
        // Already located.
        if ($item['social'] == $default['social'] && !in_array($saved_delta, $map)) {
          $new_items[$default_delta] = $items_array[$saved_delta];
          $map[$default_delta] = $saved_delta;
          $set = TRUE;
          continue 2;
        }
      }
      if (!$set) {
        $new_items[$default_delta] = $default;
      }
    }
    $items->setValue($new_items);
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $form['#wrapper_id'] = Html::getUniqueID($items->getName());
    // Just in this case is important to preserve default value order.
    if (!$this->getSetting('select_social') && $this->cardinality !== FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && stripos($this->routeName, 'entity.field_config') === FALSE) {
      $this->ensureItemsOrder($items);
    }

    $elements = parent::formMultipleElements($items, $form, $form_state);

    $elements['#prefix'] = '<div id="' . $form['#wrapper_id'] . '">';
    $elements['#suffix'] = '</div>';
    $elements['add_more']['#ajax']['wrapper'] = $form['#wrapper_id'];

    if ($this->getSetting('disable_weight')) {
      // Disable item order change.
      $elements['#theme'] = 'field_multiple_value_no_draggable_form';
    }

    return $elements;
  }

  /**
   * Submit callback to remove an item from the field UI multiple wrapper.
   *
   * @param array $form
   *   The form structure where widgets are being attached to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function removeSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $delta = $button['#delta'];
    $address = array_slice($button['#array_parents'], 0, -4);
    $address_state = array_slice($button['#parents'], 0, -3);
    $parent_element = NestedArray::getValue($form, array_merge($address, ['widget']));
    $field_name = $parent_element['#field_name'];
    $parents = $parent_element['#field_parents'];
    $field_state = static::getWidgetState($parents, $field_name, $form_state);

    for ($i = $delta; $i <= $field_state['items_count']; $i++) {
      $old_element_address = array_merge($address, ['widget', $i + 1]);
      $old_element_state_address = array_merge($address_state, [$i + 1]);
      $new_element_state_address = array_merge($address_state, [$i]);
      $moving_element = NestedArray::getValue($form, $old_element_address);
      $moving_element_value = NestedArray::getValue($form_state->getValues(), $old_element_state_address);
      $moving_element_input = NestedArray::getValue($form_state->getUserInput(), $old_element_state_address);
      $moving_element_field = NestedArray::getValue($form_state->get('field_storage'), array_merge(['#parents'], $address));
      $moving_element['#parents'] = $new_element_state_address;
      $form_state->setValueForElement($moving_element, $moving_element_value);
      $user_input = $form_state->getUserInput();
      NestedArray::setValue($user_input, $moving_element['#parents'], $moving_element_input);
      $form_state->setUserInput($user_input);
      NestedArray::setValue($form_state->get('field_storage'), array_merge(['#parents'], $moving_element['#parents']), $moving_element_field);
    }

    if ($field_state['items_count'] > 0) {
      $field_state['items_count']--;
    }

    static::setWidgetState($parents, $field_name, $form_state, $field_state);
    $form_state->setRebuild();
  }

  /**
   * Ajax callback to remove a field collection from a multi-valued field.
   *
   * @param array $form
   *   The form structure where widgets are being attached to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AjaxResponse object.
   */
  public function ajaxRemove(array $form, FormStateInterface &$form_state) {
    $button = $form_state->getTriggeringElement();
    $parent = NestedArray::getValue(
      $form,
      array_slice($button['#array_parents'], 0, -3)
    );

    return $parent;
  }

  /**
   * Provide default form values.
   *
   * @param bool $enable_social
   *   Necessity to show or hide social select.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return string[]
   *   Returns default item values.
   */
  protected function getFormValues($enable_social, FieldItemListInterface $items, $delta, FormStateInterface $form_state) {
    $entity_values = $items[$delta];
    $default_values = $this->fieldDefinition->getDefaultValueLiteral();

    $is_ajax_callback = $form_state->isRebuilding();
    $apply_items_value = !$is_ajax_callback && $entity_values->social;
    $apply_default_value = !$is_ajax_callback && $items->isEmpty() && !empty($default_values) && isset($default_values[$delta]);
    // Values must be obtained from form state when is rebuilding.
    $form_state_raw = $form_state->getValue($this->fieldDefinition->getName());
    $form_state_default = !empty($form_state_raw) && !empty($form_state_raw[$delta]) ? $form_state_raw[$delta] : NULL;
    $apply_state_value = $is_ajax_callback && !empty($form_state_default);

    // From form config.
    if ($apply_default_value) {
      $social = $default_values[$delta]['social'];
      $link = $default_values[$delta]['link'];
    }
    // From items.
    else if ($apply_items_value) {
      $social = $entity_values->social;
      $link = $entity_values->link;
    }
    // From form state.
    else if ($apply_state_value) {
      $social = $form_state_default['social'];
      $link = $form_state_default['link'];
    }
    else {
      $social = '';
      $link = '';
    }

    return ['social' => $social, 'link' => $link];
  }

  /**
   * Get element link.
   *
   * @param $form_element
   *   The array form.
   * @param $parents
   *   The array of the keys.
   *
   * @return array
   *   Return the element link.
   */
  public function getElementLink($form_element, $parents) {
    $next_index_key = count($parents) -1 ;
    $result = $form_element[$parents[0]];

    // Go to depth through the parent keys.
    while ( $next_index_key !==0) {
      $result = $result[$parents[count($parents) - $next_index_key]];
      $next_index_key--;
    }

    return $result['link'] ?? [];
  }

}
