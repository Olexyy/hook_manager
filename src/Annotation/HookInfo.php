<?php

namespace Drupal\hook_manager\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a HookInfo item annotation object.
 *
 * @see \Drupal\hook_manager\Service\HookManager
 *
 * @Annotation
 */
class HookInfo extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The hooks declaration.
   *
   * @var array
   */
  public $hooks = [];
}