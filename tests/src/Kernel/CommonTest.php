<?php

namespace Drupal\Tests\scheduled_publish\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\workflows\Entity\Workflow;

class CommonTest extends FieldKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'taxonomy',
    'scheduled_publish',
    'content_moderation',
    'workflows',
    'ultimate_cron',
    'datetime',
  ];

  /** @var \Drupal\scheduled_publish\Service\ScheduledPublishCron */
  private $scheduledUpdateService;


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig([
      'field',
      'system',
      'content_moderation',
      'scheduled_publish',
    ]);
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('content_moderation_state');
    $this->installConfig('content_moderation');

    $this->scheduledUpdateService = \Drupal::service('scheduled_publish.update');
    $this->createNodeType();
  }

  /**
   * Creates a page node type to test with, ensuring that it's moderated.
   */
  protected function createNodeType() {


    $field_storage = FieldStorageConfig::create([
      'field_name'  => 'field_scheduled_publish',
      'type'        => 'scheduled_publish',
      'entity_type' => 'node',
    ]);
    $field_storage->save();

    $node_type = NodeType::create([
      'type' => 'page',
    ]);
    $node_type->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name'  => 'field_scheduled_publish',
      'bundle'      => 'page',
      'label'       => 'Test field',
    ])->save();

    $workflow = Workflow::load('editorial');
    $workflow->getTypePlugin()->addEntityTypeAndBundle('node', 'page');
    $workflow->save();
  }

  public function testUpdateModerationState() {

    $page = Node::create([
      'type'  => 'page',
      'title' => 'A',
    ]);

    $page->moderation_state->value = 'draft';
    $page->set('field_scheduled_publish', [
      'moderation_state' => 'published',
      'value'            => '2007-12-24T18:21Z',
    ]);
    $page->save();

    $nodeID = $page->id();

    self::assertTrue($nodeID);

    $this->scheduledUpdateService->doUpdate();

    $loadedNode = Node::load($nodeID);

    self::assertEquals($loadedNode->moderation_state->value, 'published');
  }

  public function testUpdateModerationStateFuture() {

    $page = Node::create([
      'type'  => 'page',
      'title' => 'A',
    ]);

    $page->moderation_state->value = 'draft';
    $page->set('field_scheduled_publish', [
      'moderation_state' => 'published',
      'value'            => '2100-12-24T18:21Z',
    ]);
    $page->save();

    $nodeID = $page->id();

    self::assertTrue($nodeID);

    $this->scheduledUpdateService->doUpdate();

    $loadedNode = Node::load($nodeID);

    self::assertEquals($loadedNode->moderation_state->value, 'draft');
  }
}
