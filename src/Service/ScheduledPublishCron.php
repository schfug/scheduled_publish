<?php

namespace Drupal\scheduled_publish\Service;

use DateTime;
use DateTimeZone;
use Drupal\Component\Datetime\Time;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;

class ScheduledPublishCron {

  /**
   * @var EntityTypeBundleInfo
   */
  private $entityBundleInfoService;

  /**
   * @var EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * @var EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * @var Time
   */
  private $dateTime;

  public function __construct(EntityTypeBundleInfoInterface $entityBundleInfo, EntityFieldManagerInterface $entityFieldManager, EntityTypeManagerInterface $entityTypeManager, TimeInterface $dateTime) {
    $this->entityBundleInfoService = $entityBundleInfo;
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->dateTime = $dateTime;
  }

  public function doUpdate(): void {
    $bundles = $this->entityBundleInfoService->getBundleInfo('node');

    foreach ($bundles as $bundleName => $value) {

      $scheduledFields = $this->getScheduledFields($bundleName);
      if (\count($scheduledFields) > 0) {
        $query = $this->entityTypeManager->getStorage('node')->getQuery('AND');
        $query->condition('type', $bundleName);
        $nodes = $query->execute();
        foreach ($nodes as $nodeId) {
          /** @var \Drupal\node\Entity\Node $node */
          $node = Node::load($nodeId);
          foreach ($scheduledFields as $scheduledField) {
            $this->updateNodeField($node, $scheduledField);
          }
        }
      }
    }
  }

  private function getScheduledFields(string $bundleName): array {
    $scheduledFields = [];
    $fields = $this->entityFieldManager
      ->getFieldDefinitions('node', $bundleName);
    foreach ($fields as $fieldName => $field) {
      /** @var FieldConfig $field */
      if (strpos($fieldName, 'field_') !== FALSE) {
        if ($field->getType() === 'scheduled_publish') {
          $scheduledFields[] = $fieldName;
        }
      }
    }
    return $scheduledFields;
  }

  private function updateNodeField(Node $node, string $scheduledField): void {
    /** @var FieldItemList $scheduledEntity */
    $scheduledEntity = $node->get($scheduledField);
    $scheduledValue = $scheduledEntity->getValue();
    if (empty($scheduledValue)) {
      return;
    }
    $currentModerationState = $node->get('moderation_state')
      ->getValue()[0]['value'];
    $scheduledEntityStore = $scheduledValue;

    if ($currentModerationState === $scheduledEntityStore[0]['moderation_state']) {
      $this->updateNode($node, $scheduledEntityStore[0]['moderation_state'], $scheduledField);
      return;
    }
    $timestamp = $this->getTimestampFromIso8601($scheduledEntityStore[0]['value']);

    if ($timestamp - $this->dateTime->getCurrentTime() <= 0) {
      $this->updateNode($node, $scheduledEntityStore[0]['moderation_state'], $scheduledField);
    }
  }

  private function getTimestampFromIso8601(string $dateIso8601): int {
    $datetime = new DateTime($dateIso8601, new DateTimeZone(drupal_get_user_timezone()));
    return $datetime->getTimestamp();
  }

  private function updateNode(Node $node, string $moderationState, string $scheduledPublishField): void {
    $node->set($scheduledPublishField, NULL);
    $node->set('moderation_state', $moderationState);
    $node->save();
  }

}