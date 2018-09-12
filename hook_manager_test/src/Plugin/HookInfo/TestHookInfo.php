<?php

namespace Drupal\hook_manager_test\Plugin\HookInfo;

use Drupal\hook_manager\Plugin\HookInfoBase;

/**
 * Example hook info.
 *
 * Be careful: 'id' should be unique in project scope, else plugins override.
 * I propose set module name as identifier.
 * In 'hooks' array we define 'canonical' hook name, that begins with 'hook_'.
 * Conventional for methods, that implement hooks, is that they should be
 * camel case analogs of 'canonical' hook name.
 * Value in 'hooks' array is priority compare to other plugins for this hook.
 *
 * @HookInfo(
 *  id = "hook_manager_test",
 *  hooks = {
 *    "hook_hook_manager_test_invoke" = 0,
 *    "hook_hook_manager_test_invoke_all" = 0,
 *    "hook_hook_manager_test_alter" = 0,
 *  },
 * )
 */
class TestHookInfo extends HookInfoBase {

  /**
   * Result function.
   *
   * @return string
   *   Result.
   */
  public static function hookResultInvoke() {

    return 'ok';
  }

  /**
   * Result function.
   *
   * @return array
   *   Result.
   */
  public static function hookResultInvokeAll() {

    return  [
      'ok' => 'ok',
    ];
  }

  /**
   * Implements hook_hook_manager_test_invoke().
   */
  public function hookHookManagerTestInvoke() {

    return static::hookResultInvoke();
  }

  /**
   * Implements hook_hook_manager_test_invoke_all().
   */
  public function hookHookManagerTestInvokeAll() {

    return static::hookResultInvokeAll();
  }

  /**
   * Implements hook_form_alter().
   */
  public function hookHookManagerTestAlter(array &$data) {

    $data['ok'] = 'ok';
  }

}
