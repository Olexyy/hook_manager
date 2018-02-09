<?php

namespace Drupal\hook_manager\Service;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\hook_manager\Plugin\HookPluginManagerBase;

/**
 * Class HookManager.
 *
 * @package Drupal\hook_manager\Plugin
 */
class HookManager extends HookPluginManagerBase {

  /**
   * HookManager constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces,
    CacheBackendInterface $cache_backend) {
    parent::__construct('Plugin/HookInfo', $namespaces,
      'Drupal\hook_manager\Plugin\HookInfo\HookInfoInterface',
        'Drupal\hook_manager\Annotation\HookInfo');
    //$this->alterInfo('hook_manager_plugin_info');
    $this->setCacheBackend($cache_backend, 'hook_manager_plugins');
  }

  /**
   * Invokes hook in given plugin defined in id.
   *
   * @param string $id
   *   Plugin identifier.
   * @param string $name
   *   Expected non canonical name.
   * @param array $args
   *   Array of arguments to be passed to hook.
   *
   * @return array
   *   Results of invocation.
   */
  public function invoke($id, $name, array $args = []) {
    $hookName = $this->getHookCanonicalName($name);
    if ($definition = $this->definitionImplements($id, $hookName)) {
      return $this->invokeDefinition($hookName, $definition, $args);
    }
    return NULL;
  }

  /**
   * Invokes hook in all plugins.
   *
   * @param string $name
   *   Expected non canonical name.
   * @param array $args
   *   Array of arguments to be passed to hook.
   *
   * @return array
   *   Results of invocation.
   */
  public function invokeAll($name, array $args = []) {
    $hookName = $this->getHookCanonicalName($name);
    $return = [];
    foreach ($this->definitionsImplement($hookName) as $definition) {
      $result = $this->invokeDefinition($hookName, $definition, $args);
      if (isset($result) && is_array($result)) {
        $return = NestedArray::mergeDeep($return, $result);
      }
      elseif (isset($result)) {
        $return[] = $result;
      }
    }
    return $return;
  }

  /**
   * Invokes alter hook.
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {
    if (is_string($type)) {
      $type = [$type];
    }
    if (is_array($type)) {
      foreach ($type as $name) {
        $hookName = $this->getHookAlterCanonicalName($name);
        foreach ($this->definitionsImplement($hookName) as $definition) {
          $this->invokeDefinitionAlter($hookName, $definition, $data, $context1, $context2);
        }
      }
    }
  }

  /**
   * Analog of 'module_implements'.
   *
   * For internal usage.
   *
   * @param string $id
   *   Plugin id.
   * @param string $hookName
   *   Hook canonical name.
   *
   * @return array|null
   *   Definition if any.
   */
  private function definitionImplements($id, $hookName) {
    if ($definition = $this->getDefinition($id)) {
      if (array_key_exists($hookName, $definition['hooks'])) {
        return $definition;
      }
    }
    return NULL;
  }

  /**
   * Analog of 'module_implements' multiple.
   *
   * For internal usage.
   *
   * @param string $hookName
   *   Hook canonical name.
   *
   * @return array
   *   Of plugin definitions.
   */
  private function definitionsImplement($hookName) {
    static $definitions = [];
    if (!$definitions) {
      $definitions = $this->getDefinitions();
    }
    $implementations = [];
    foreach ($definitions as $definition) {
      if (array_key_exists($hookName, $definition['hooks'])) {
        $implementations[] = $definition;
        $definition['priority'] = $this->normalizePriority($definition['hooks'][$hookName]);
      }
    }
    $this->sort($implementations);
    return $implementations;
  }

  /**
   * Internal invocation for plugin definition.
   *
   * @param string $hookName
   *   Expected hook_name.
   * @param array $definition
   *   Definition array.
   * @param array $args
   *   Array of arguments to be passed to hook.
   *
   * @return mixed|null
   *   Invocation result.
   */
  private function invokeDefinition($hookName, array $definition, array $args) {
    try {
      $hookImplementation = $this->createInstance($definition['id']);
      $method = $this->toCamelCase($hookName);
      if (method_exists($hookImplementation, $method)) {
        return call_user_func_array($hookImplementation->$method, $args);
      }
      return NULL;
    }
    catch (\Exception $exception) {
      return NULL;
    }
  }

  /**
   * Internal invocation for plugin definition.
   *
   * @param string $hookName
   *   Expected hook_name.
   * @param array $definition
   *   Definition array.
   * @param mixed $data
   *   Data to be altered.
   * @param mixed $context1
   *   Alterable context.
   * @param mixed $context2
   *   Alterable context.
   */
  private function invokeDefinitionAlter($hookName, array $definition, &$data, &$context1, &$context2) {
    try {
      $hookImplementation = $this->createInstance($definition['id']);
      $method = $this->toCamelCase($hookName);
      if (method_exists($hookImplementation, $method)) {
        $hookImplementation->$method($data, $context1, $context2);
      }
    }
    catch (\Exception $exception) {
    }
  }

  /**
   * Sorting for hook info definitions.
   *
   * @param array $definitions
   *   Hook info definitions.
   */
  private function sort(array &$definitions) {
    uasort($definitions, [$this, 'comparator']);
  }

  /**
   * Sorting comparator.
   */
  private function comparator(array $a, array $b) {
    return $a['priority'] - $b['priority'];
  }

  /**
   * Internal helper.
   *
   * @param string $hookName
   *   Expected 'hook_name'.
   *
   * @return string
   *   Expected 'hookName'.
   */
  private function toCamelCase($hookName) {
    $parts = explode('_', $hookName);
    array_walk($parts, function (&$part, $key) {
      if ($key) {
        ucfirst($part);
      }
    });
    return implode('', $parts);
  }

  /**
   * Getter for canonical hook name.
   *
   * @param string $name
   *   Given hook name.
   *
   * @return string
   *   Canonical hook name.
   */
  private function getHookCanonicalName($name) {
    return 'hook_' . $name;
  }

  /**
   * Getter for alter canonical hook name.
   *
   * @param string $name
   *   Given hook name.
   *
   * @return string
   *   Canonical alter hook name.
   */
  private function getHookAlterCanonicalName($name) {
    return 'hook_' . $name . '_alter';
  }

  /**
   * Internal helper to normalize given priorities.
   *
   * @param mixed $value
   *   Should accept any possible input.
   *
   * @return int
   *   Integer value.
   */
  private function normalizePriority($value) {
    if (is_numeric($value)) {
      return (int) $value;
    }
    return 0;
  }

}
