<?php

namespace Drupal\social_link_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'social_links' entity field type.
 *
 * @FieldType(
 *   id = "social_links",
 *   label = @Translation("Social Links"),
 *   description = @Translation("An entity field with social links."),
 *   default_widget = "social_links",
 *   default_formatter = "font_awesome",
 * )
 */
class SocialLinkItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['social'] = DataDefinition::create('string')
      ->setLabel(t('Social network'))
      ->setRequired(FALSE);
    $properties['link'] = DataDefinition::create('string')
      ->setLabel(t('Profile link'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema['columns'] = [
      'social' => [
        'type' => 'varchar',
        'length' => 255,
      ],
      'link' => [
        'type' => 'varchar',
        'length' => 255,
      ],
    ];

    return $schema;
  }

/**
 * {@inheritdoc}
 */
  public function isEmpty() {
    $value = $this->get('link')->getValue();
    $social = $this->get('social')->getValue();

    // Allow to save just social without link for default value in field config.
    return empty($value) && empty($social);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $link = $this->get('link')->getValue();
    $social = $this->get('social')->getValue();
    // Do not save if network or link are empty (just works in real entity field
    // save process, not in field config form).
    if (empty($link) && !empty($social)) {
      $this->set('social', '');
    }
    if (empty($social) && !empty($link)) {
      $this->set('social', '');
    }

    parent::preSave();
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'link';
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'platforms' => [],
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);
    $platforms_service = \Drupal::service('plugin.manager.social_link_field.platform');
    $options = [];
    foreach ($platforms_service->getPlatforms() as $platform) {
      $options[$platform['id']] = $platform['name']->getUntranslatedString();
    }
    $platforms = $this->getSetting('platforms') ?? [];
    $form['platforms'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed Platforms'),
      '#options' => $options,
      '#default_value' => $platforms,
      '#description' => $this->t('Select none to allow all'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $constraints[] = $constraint_manager->create('ComplexData', [
      'link' => [
        'Regex' => [
          'message' => $this->t('Just add the part of your profile. Do not include preceding slash.'),
          // Validate not starting with "http://", "https://", "www." or "/".
          'pattern' => '/^(?!(http:\/\/|https:\/\/|www.|\/))/i',
        ],
      ],
    ]);

    return $constraints;
  }

}
