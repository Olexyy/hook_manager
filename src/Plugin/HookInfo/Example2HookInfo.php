<?php

namespace Drupal\hook_manager\Plugin\HookInfo;

/**
 * Hook info example 2.
 *
 * @HookInfo(
 *  id = "example_hooks2",
 *  hooks = {"hook_theme" = 5,},
 * )
 */
class Example2HookInfo extends HookInfoBase {

  /**
   * Implements hook_theme().
   */
  public function hookMenu() {
    return [
      'theme2' => [ ]
    ];
  }

}