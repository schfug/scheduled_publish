<?php

namespace Drupal\scheduled_publish\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\datetime\DateTimeComputed;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of the 'scheduled_publish_type' field type.
 *
 * @FieldType(
 *   id = "scheduled_publish",
 *   label = @Translation("Scheduled publish"),
 *   description = @Translation("Scheduled publish"),
 *   default_widget = "scheduled_publish",
 *   default_formatter = "scheduled_publish_formatter"
 * )
 */
class ScheduledPublish extends DateTimeItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties = parent::propertyDefinitions($field_definition);
    $properties['moderation_state'] = DataDefinition::create('string')
      ->setLabel(t('The moderation state.'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['moderation_state'] = [
      'type'   => 'varchar',
      'length' => 32,
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->get('moderation_state')
        ->getValue()) || empty($this->get('value')->getValue());
  }

}
