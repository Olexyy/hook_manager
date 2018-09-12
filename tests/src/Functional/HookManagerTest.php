<?php

namespace Drupal\Tests\admin_toolbar\Functional;

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

    $result = $this->moduleHandler->invoke('hook_manager_test', 'hook_manager_test_invoke');
    $this->assertEquals(TestHookInfo::hookResultInvoke(), $result, 'Hook info triggered');
    $result = $this->moduleHandler->invokeAll('hook_manager_test_invoke_all');
    $this->assertEquals(TestHookInfo::hookResultInvokeAll(), $result, 'Hook info triggered');
    $data = [];
    $this->moduleHandler->alter('hook_manager_test_alter', $data);
    $this->assertNotEmpty($data['ok'], 'Hook alter triggered');
  }

}
