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
   * Static hook definitions list.
   *
   * @var array
   */
  protected $hookDefinitions = [];

  /**
   * Static alter hook definitions map.
   *
   * @var array
   */
  protected $hooksDefinitionsAlter = [];

  /**
   * HookManager constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces,
    CacheBackendInterface $cache_backend) {
    parent::__construct('Plugin/HookInfo', $namespaces,
      'Drupal\hook_manager\Plugin\HookInfoInterface',
        'Drupal\hook_manager\Annotation\HookInfo');
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

    if ($hookName = $this->getHookCanonicalName($name)) {
      if ($definition = $this->definitionImplements($id, $hookName)) {
        return $this->invokeDefinition($hookName, $definition, $args);
      }
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

    $return = [];
    if ($hookName = $this->getHookCanonicalName($name)) {
      foreach ($this->definitionsImplement($hookName) as $definition) {
        $result = $this->invokeDefinition($hookName, $definition, $args);
        if (isset($result) && is_array($result)) {
          $return = NestedArray::mergeDeep($return, $result);
        }
        elseif (isset($result)) {
          $return[] = $result;
        }
      }
    }

    return $return;
  }

  /**
   * Invokes alter hook.
   *
   * @param string|array $type
   *   Expected non canonical name or list.
   * @param mixed $data
   *   Data passed by reference.
   * @param mixed|null $context1
   *   Context1 passed by reference.
   * @param mixed|null $context2
   *   Context2 passed by reference.
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {

    if (is_string($type)) {
      $type = [$type];
    }
    if (is_array($type)) {
      foreach ($type as $name) {
        if ($hookName = $this->getHookAlterCanonicalName($name)) {
          if (!isset($this->hooksDefinitionsAlter[$hookName])) {
            $this->hooksDefinitionsAlter[$hookName] = $this->definitionsImplement($hookName);
          }
          foreach ($this->hooksDefinitionsAlter[$hookName] as $definition) {
            $this->invokeDefinitionAlter($hookName, $definition, $data, $context1, $context2);
          }
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

    if (!$this->hookDefinitions) {
      $this->hookDefinitions = $this->getDefinitions();
    }
    $implementations = [];
    foreach ($this->hookDefinitions as $definition) {
      if (array_key_exists($hookName, $definition['hooks'])) {
        $implementations[] = [
          'id' => $definition['id'],
          'priority' => $this->normalizePriority($definition['hooks'][$hookName]),
        ];
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
        return call_user_func_array([$hookImplementation, $method], $args);
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
        $hookImplementation->{$method}($data, $context1, $context2);
      }
    }
    catch (\Exception $exception) { }

  }

  /**
   * Sorting for hook info definitions.
   *
   * @param array $definitions
   *   Hook info definitions.
   */
  private function sort(array &$definitions) {
    usort($definitions, [$this, 'comparator']);
  }

  /**
   * Sorting comparator.
   */
  private function comparator(array $a, array $b) {

    if ($a['priority'] == $b['priority']) {
      return 0;
    }

    return $a['priority'] < $b['priority']? 1 : -1;
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
        $part = ucfirst($part);
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

    if($name) {
      return 'hook_' . $name;
    }

    return '';
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

    if($name) {
      return 'hook_' . $name . '_alter';
    }

    return '';
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
