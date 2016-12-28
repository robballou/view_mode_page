<?php

namespace Drupal\view_mode_page\Plugin\view_mode_page\AliasType;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\view_mode_page\AliasTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A view_mode_page alias type plugin for entities with canonical links.
 *
 * @AliasType(
 *   id = "canonical_entities",
 *   deriver = "\Drupal\view_mode_page\Plugin\Derivative\EntityAliasTypeDeriver"
 * )
 */
class EntityAliasTypeBase extends ContextAwarePluginBase implements AliasTypeInterface, ContainerFactoryPluginInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityAliasTypeBase instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    $definition = $this->getPluginDefinition();
    // Cast the admin label to a string since it is an object.
    // @see \Drupal\Core\StringTranslation\TranslationWrapper
    return (string) $definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenTypes() {
    $definition = $this->getPluginDefinition();
    return $definition['types'];
  }

  /**
   * Returns the entity type ID.
   *
   * @return string
   *   The entity type ID.
   */
  protected function getEntityTypeId() {
    return $this->getDerivativeId();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = [];
    $dependencies['module'][] = $this->entityTypeManager->getDefinition($this->getEntityTypeId())->getProvider();
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($object) {
    return $object instanceof FieldableEntityInterface && $object->getEntityTypeId() == $this->getEntityTypeId();
  }

}
