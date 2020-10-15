<?php

declare(strict_types = 1);

namespace Drupal\Tests\og_demo_module\Functional;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\og\Og;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests subscribe tip on group canonical.
 *
 * @group og_demo_module
 */
class SubscribeTipTest extends BrowserTestBase {
  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'og'];

  /**
   * Test entity group.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $group1;

  /**
   * Test normal user with no connection to the organic group.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $normalUser;

  /**
   * A group bundle name.
   *
   * @var string
   */
  protected $groupBundle1;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->groupBundle1 = mb_strtolower($this->randomMachineName());
    NodeType::create(['type' => $this->groupBundle1])->save();

    // Define the entities as groups.
    Og::groupTypeManager()->addGroup('node', $this->groupBundle1);

    // Create node author user.
    $user = $this->createUser();

    $this->group1 = Node::create([
      'type' => $this->groupBundle1,
      'title' => $this->randomString(),
      'uid' => $user->id(),
    ]);
    $this->group1->save();

    $this->normalUser = $this->drupalCreateUser();
  }

  /**
   * Tests subscribe tip.
   */
  public function testSubscribeTip() {
    $this->drupalLogin($this->normalUser);
    $this->drupalPlaceBlock('system_messages_block');

    $this->drupalGet(Url::fromRoute('entity.node.canonical', ['node' => $this->group1->id()]));
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->responseContains(t('Hi %account, click here %subscribe if you would like to subscribe to this group called %group', [
      '%account' => $this->normalUser->getAccountName(),
      '%subscribe' => Url::fromRoute('og.subscribe', ['entity_type_id' => $this->groupBundle1])->toString(),
      '%group' => $this->group1->label(),
    ]));
  }
}
