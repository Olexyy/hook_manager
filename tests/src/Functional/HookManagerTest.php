<?php

namespace Drupal\Tests\hook_manager\Functional;

use Drupal\hook_manager\Service\HookManagerModuleHandler;
use Drupal\hook_manager_test\Plugin\HookInfo\TestHookInfo;
use Drupal\Tests\BrowserTestBase;

/**
 * Test hook manager module.
 *
 * @group hook_manager
 */
class HookManagerTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'hook_manager',
    'hook_manager_test',
  ];

  /**
   * A test user with permission to access the administrative toolbar.
   *
   * @var HookManagerModuleHandler
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->assertInstanceOf(HookManagerModuleHandler::class, $this->moduleHandler, 'Service injected.');
  }

  /**
   * Tests hooks invocations.
   */
  public function testHooksInvocation() {

    $result = $this->moduleHandler->invokePlugin('hook_manager_test', 'hook_manager_test_invoke');
    $this->assertEquals(TestHookInfo::hookResult(), $result, 'Hook invoke triggered');
    $result = $this->moduleHandler->invokeAll('hook_manager_test_invoke_all');
    $this->assertEquals(TestHookInfo::hookResult(), $result, 'Hook invoke all triggered');
    $data = [];
    $this->moduleHandler->alter('hook_manager_test', $data);
    $this->assertEquals(TestHookInfo::hookResult(), $data, 'Hook alter triggered');
  }

}
