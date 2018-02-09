<?php

namespace Drupal\hook_manager\Plugin\HookInfo;

  use Drupal\Component\Plugin\PluginBase;
  use Drupal\Core\Entity\EntityTypeManager;
  use Drupal\Core\Logger\LoggerChannelFactory;
  use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
  use Symfony\Component\DependencyInjection\ContainerInterface;
  use Symfony\Component\HttpFoundation\RequestStack;

  /**
   * Base class for Importer plugins.
   */
abstract class HookInfoBase extends PluginBase implements
  HookInfoInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Request stack.
   *
   * @var RequestStack
   */
  protected $requestStack;

  /**
   * Logger service.
   *
   * @var LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id,
    $plugin_definition, EntityTypeManager $entityTypeManager,
    RequestStack $requestStack, LoggerChannelFactory $loggerFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
    $this->loggerFactory = $loggerFactory;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array
  $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('logger.factory')
    );
  }

}