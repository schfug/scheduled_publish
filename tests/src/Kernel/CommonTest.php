<?php

namespace Drupal\Tests\scheduled_publish\Kernel;

use Drupal\degov_common\Common;
use Drupal\degov_common\Entity\NodeService;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

class CommonTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'system',
    'node',
    'paragraphs',
    'degov_common',
    'config_replace',
    'video_embed_field',
    'paragraphs',
    'file',
    'text',
    'taxonomy',
    'degov_node_normal_page',
    'degov_scheduled_updates',
  ];

  /** @var \Drupal\degov_common\Entity\EntityService */
  private $entityService;

  /** @var \Drupal\scheduled_publish\Service\ScheduledPublishCron */
  private $scheduledUpdateService;


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    \Drupal::moduleHandler()->loadInclude('paragraphs', 'install');
    \Drupal::moduleHandler()->loadInclude('taxonomy', 'install');
    $this->entityService = \Drupal::service('degov_common.entity');
    $this->scheduledUpdateService = \Drupal::service('scheduled_publish.update');
  }

  public function testUpdateModerationState() {
    $node = Node::create([
      'title'             => 'An article node',
      'type'              => 'normal_page',
      'moderation_state'  => 'draft',
      'scheduled_publish' => [
        'moderation_state' => 'published',
        'value'            => '2007-12-24T18:21Z',
      ],
    ]);
    $node->save();

    $nodeID = $this->entityService->load('node', [
      'title' => 'An article node',
      'type'  => 'normal_page',
    ]);
    self::assertTrue($nodeID);

    $this->scheduledUpdateService->doUpdate();

    $nodeID = $this->entityService->load('node', [
      'title'            => 'An article node',
      'moderation_state' => 'published',
      'type'             => 'normal_page',
    ]);
    self::assertTrue($nodeID);
  }

}
