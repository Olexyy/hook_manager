<?php

namespace Drupal\hook_manager\Service;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Class that manages modules in a Drupal installation.
 */
class HookManagerModuleHandler extends ModuleHandler {

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
    return $this->getHookManager()->invoke($id, $name, $args);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAll($hook, array $args = []) {

    $return = parent::invokeAll($hook, $args);
    $pluginResult = $this->getHookManager()->invokeAll($hook, $args);
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
    $this->getHookManager()->alter($type, $data, $context1, $context2);
  }

  /**
   * Hook manager service.
   *
   * Workaround should be found for circular reference.
   *
   * @return \Drupal\hook_manager\Service\HookManager
   */
  protected function getHookManager() {
    return \Drupal::service('hook_manager');
  }


}
