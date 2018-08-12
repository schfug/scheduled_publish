<?php

namespace Drupal\scheduled_publish\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
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
class ScheduledPublish extends DateTimeItem implements DateTimeItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['moderation_state'] = DataDefinition::create('string')
      ->setLabel(t('The moderation state.'));

    $properties['value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Date value'))
      ->setRequired(TRUE);

    $properties['date'] = DataDefinition::create('any')
      ->setLabel(t('Computed start date'))
      ->setDescription(t('The computed start DateTime object.'))
      ->setComputed(TRUE)
      ->setClass(DateTimeComputed::class)
      ->setSetting('date source', 'value');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'moderation_state' => [
          'type' => 'varchar',
          'length' => 32,
        ],
        'value' => [
          'description' => 'The date value.',
          'type' => 'varchar',
          'length' => 20,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isEmpty() {
    return empty($this->get('moderation_state')->getValue()) || empty($this->get('value')->getValue());
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE) {
    // Enforce that the computed date is recalculated.
    if ($property_name === 'value') {
      $this->date = NULL;
    }
    parent::onChange($property_name, $notify);
  }

}
