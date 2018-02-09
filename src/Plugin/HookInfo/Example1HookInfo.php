<?php

namespace Drupal\hook_manager\Plugin\HookInfo;

use Drupal\Core\Form\FormStateInterface;
use Drupal\hook_manager\Plugin\HookInfoBase;

/**
 * Example hook info.
 *
 * Be careful: 'id' should be unique in project scope.
 * In 'hooks' array we define 'canonical' hook name, that begin with 'hook_'.
 * Conventional for methods that implement hooks is that they should be
 * camel case analogs of 'canonical' hook name.
 * Value in this array is priority among other plugins for this hook.
 *
 * @HookInfo(
 *  id = "example_hooks1",
 *  hooks = {
 *    "hook_theme" = 0,
 *    "hook_form_alter" = 0,
 *  },
 * )
 */
class Example1HookInfo extends HookInfoBase {

  /**
   * Implements hook_theme().
   */
  public function hookMenu() {
    $theme = [
      'theme2' => [],
    ];
    return $theme;
  }

  /**
   * Implements hook_form_alter().
   */
  public function hookFormAlter(array &$form, FormStateInterface $formState, $formId) {
    $form['foo'] = 'bar';
  }

}
