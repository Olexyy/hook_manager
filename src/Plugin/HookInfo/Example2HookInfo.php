<?php

namespace Drupal\hook_manager\Plugin\HookInfo;

use Drupal\Core\Form\FormStateInterface;
use Drupal\hook_manager\Plugin\HookInfoBase;

/**
 * Example hook info.
 *
 * Be careful: 'id' should be unique in project scope, else plugins override.
 * In 'hooks' array we define 'canonical' hook name, that begins with 'hook_'.
 * Conventional for methods, that implement hooks, is that they should be
 * camel case analogs of 'canonical' hook name.
 * Value in 'hooks' array is priority compare to other plugins for this hook.
 *
 * @HookInfo(
 *  id = "example_hooks_2",
 *  hooks = {
 *    "hook_form_alter" = 0,
 *    "hook_token_info" = 10,
 *    "hook_block_build_alter" = 0,
 *  },
 * )
 */
class Example2HookInfo extends HookInfoBase {

  /**
   * Implements hook_token_info().
   */
  public function hookTokenInfo() {
    $a = 1;
  }

  /**
   * Implements hook_form_alter().
   */
  public function hookFormAlter(array &$form, FormStateInterface $formState, $formId) {
    $form['foo'] = 'bar';
  }

  /**
   * Implements hook_query_alter().
   */
  public function hookBlockBuildAlter(&$data, &$context) {
    $a = $data;
  }

}
