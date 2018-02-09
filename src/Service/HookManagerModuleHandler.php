<?php

namespace Drupal\hook_manager\Service;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Class that manages modules in a Drupal installation.
 */
class HookManagerModuleHandler extends ModuleHandler {

  /**
   * Hook manager service.
   *
   * @var \Drupal\hook_manager\Service\HookManager
   */
  protected $hookManager;

  /**
   * HookManagerModuleHandler constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(string $root, array $module_list,
    CacheBackendInterface $cache_backend, HookManager $hookManager) {
    parent::__construct($root, $module_list, $cache_backend);
    $this->hookManager = $hookManager;
  }

  /**
   * Invokes hook in given plugin by given id.
   *
   * @param string $id
   *   Plugin identifier.
   * @param string $name
   *   Expected default non canonical name.
   * @param array $args
   *   Array of arguments to be passed to hook.
   *
   * @return array
   *   Results of invocation.
   */
  public function invokePlugin($id, $name, array $args = []) {
    return $this->hookManager->invoke($id, $name, $args);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAll($hook, array $args = []) {

    $return = parent::invokeAll($hook, $args);
    $pluginResult = $this->hookManager->invokeAll($hook, $args);
    if (isset($pluginResult) && is_array($pluginResult)) {
      $return = NestedArray::mergeDeep($return, $pluginResult);
    }
    elseif (isset($pluginResult)) {
      $return[] = $pluginResult;
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {
    parent::alter($type, $data, $context1, $context2);
    $this->hookManager->alter($type, $data, $context1, $context2);
  }

}
