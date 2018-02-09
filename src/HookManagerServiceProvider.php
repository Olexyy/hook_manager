<?php

namespace Drupal\hook_manager;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies the alias manager service.
 */
class HookManagerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides module_handler service.
    $definition = $container->getDefinition('module_handler');
    $definition->setClass('Drupal\hook_manager\Service\HookManagerModuleHandler')
      ->addArgument(new Reference('hook_manager'));
  }

}
