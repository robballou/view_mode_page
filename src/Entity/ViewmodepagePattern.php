<?php

namespace Drupal\view_mode_page\Entity;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\view_mode_page\ViewmodepagePatternInterface;

/**
 * Defines the Viewmodepage pattern entity.
 *
 * @ConfigEntityType(
 *   id = "view_mode_page_pattern",
 *   label = @Translation("Viewmodepage pattern"),
 *   handlers = {
 *     "list_builder" = "Drupal\view_mode_page\Form\PatternListBuilder",
 *     "form" = {
 *       "default" = "Drupal\view_mode_page\Form\PatternEditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "pattern",
 *   admin_permission = "administer view_mode_page",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "type",
 *     "pattern",
 *     "view_mode",
 *     "selection_criteria",
 *     "selection_logic",
 *     "weight",
 *     "relationships"
 *   },
 *   lookup_keys = {
 *     "type"
 *   },
 *   links = {
 *     "collection" = "/admin/config/search/view-mode-page",
 *     "edit-form" = "/admin/config/search/view-mode-page/{view_mode_page_pattern}",
 *     "delete-form" = "/admin/config/search/view-mode-page/{view_mode_page_pattern}/delete"
 *   }
 * )
 */
class ViewmodepagePattern extends ConfigEntityBase implements ViewmodepagePatternInterface {
  /**
   * The Viewmodepage pattern ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Viewmodepage pattern label.
   *
   * @var string
   */
  protected $label;

  /**
   * The pattern type.
   *
   * A string denoting the type of view_mode_page pattern this is. For a
   * node path this would be 'node', for users it would be 'user', and so on.
   * This allows for arbitrary non-entity patterns to be possible if applicable.
   *
   * @var string
   */
  protected $type;

  /**
   * The default single lazy plugin collection.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $aliasTypeCollection;

  /**
   * A string for path processing.
   *
   * @var string
   */
  protected $pattern;

  /**
   * A string denoting the type of view_mode is used for rendering.
   *
   * @var string
   */
  protected $view_mode;

  /**
   * The plugin configuration for the selection criteria condition plugins.
   *
   * @var array
   */
  protected $selection_criteria = [];

  /**
   * The selection logic for this pattern entity (either 'and' or 'or').
   *
   * @var string
   */
  protected $selection_logic = 'and';

  /**
   * The weight for this position.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * The relationships.
   *
   * @var array[]
   *   Keys are context tokens, and values are arrays with the following keys:
   *   - label (string|null, optional): The human-readable label of this
   *     relationship.
   */
  protected $relationships = [];

  /**
   * The plugin collection that holds the selection criteria condition plugins.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $selectionConditionCollection;

  /**
   * {@inheritdoc}
   *
   * Not using core's default logic around ConditionPluginCollection since it
   * incorrectly assumes no condition will ever be applied twice.
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $criteria = [];
    foreach ($this->getSelectionConditions() as $id => $condition) {
      $criteria[$id] = $condition->getConfiguration();
    }
    $this->selection_criteria = $criteria;

    // Clear the cache.
    \Drupal::service('cache.data')->deleteAll();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    // Clear the cache.
    \Drupal::service('cache.data')->deleteAll();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    $this->calculatePluginDependencies($this->getAliasType());

    foreach ($this->getSelectionConditions() as $instance) {
      $this->calculatePluginDependencies($instance);
    }

    return $this->getDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function getPattern() {
    return $this->pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function getPatternRegex() {
    $pattern = $this->getPattern();
    $pattern = preg_replace('!/+!', '/', $pattern);
    $pattern = trim($pattern, '/');
    $patternArray = explode('/', $pattern);

    $patternRegex = '!^';
    foreach ($patternArray as $patternPart) {
      if ($patternPart == '%') {
        $patternRegex .= '/(.*)';
      }
      else {
        $patternRegex .= '/' . preg_quote($patternPart, '!');
      }
    }
    $patternRegex .= '$!';

    return $patternRegex;
  }

  /**
   * {@inheritdoc}
   */
  public function setPattern($pattern) {
    $this->pattern = $pattern;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewMode() {
    return $this->view_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewModeLabel() {
    $view_mode = $this->getViewMode();
    if ($entity_type_id = $this->getAliasType()->getDerivativeId()) {
      /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
      $entity_display_repository = \Drupal::service('entity_display.repository');
      $view_modes                = $entity_display_repository->getViewModeOptions($entity_type_id);
      if (!empty($view_modes[$view_mode])) {
        return $view_modes[$view_mode];
      }
    }
    return $view_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasType() {
    if (!$this->aliasTypeCollection) {
      $this->aliasTypeCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.alias_type'), $this->getType(), ['default' => $this->getPattern()]);
    }
    return $this->aliasTypeCollection->get($this->getType());
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionConditions() {
    if (!$this->selectionConditionCollection) {
      $this->selectionConditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('selection_criteria'));
    }
    return $this->selectionConditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectionCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getSelectionConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionCondition($condition_id) {
    return $this->getSelectionConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeSelectionCondition($condition_id) {
    $this->getSelectionConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionLogic() {
    return $this->selection_logic;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    $contexts = $this->getAliasType()->getContexts();
    foreach ($this->getRelationships() as $token => $definition) {
      /** @var \Drupal\ctools\TypedDataResolver $resolver */
      $resolver           = \Drupal::service('ctools.typed_data.resolver');
      $context            = $resolver->convertTokenToContext($token, $contexts);
      $context_definition = $context->getContextDefinition();
      if (!empty($definition['label'])) {
        $context_definition->setLabel($definition['label']);
      }
      $contexts[$token] = $context;
    }
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRelationship($token) {
    return isset($this->relationships[$token]);
  }

  /**
   * {@inheritdoc}
   */
  public function addRelationship($token, $label = NULL) {
    if (!$this->hasRelationship($token)) {
      $this->relationships[$token] = [
        'label' => $label,
      ];
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function replaceRelationship($token, $label) {
    if ($this->hasRelationship($token)) {
      $this->relationships[$token] = [
        'label' => $label,
      ];
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeRelationship($token) {
    unset($this->relationships[$token]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelationships() {
    return $this->relationships;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(EntityInterface $entity) {
    if ($this->getAliasType()->applies($entity)) {
      $definitions = $this->getAliasType()->getContextDefinitions();
      if (count($definitions) > 1) {
        throw new \Exception("Alias types do not support more than one context.");
      }
      $keys = array_keys($definitions);
      // Set the context object on our Alias plugin before retrieving contexts.
      $this->getAliasType()->setContextValue($keys[0], $entity);
      /** @var \Drupal\Core\Plugin\Context\ContextInterface[] $base_contexts */
      $contexts = $this->getContexts();
      /** @var \Drupal\Core\Plugin\Context\ContextHandler $context_handler */
      $context_handler = \Drupal::service('context.handler');
      $conditions      = $this->getSelectionConditions();
      foreach ($conditions as $condition) {
        if ($condition instanceof ContextAwarePluginInterface) {
          try {
            $context_handler->applyContextMapping($condition, $contexts);
          }
          catch (ContextException $e) {
            watchdog_exception('view_mode_page', $e);
            return FALSE;
          }
        }
        $result = $condition->execute();
        if ($this->getSelectionLogic() == 'and' && !$result) {
          return FALSE;
        }
        elseif ($this->getSelectionLogic() == 'or' && $result) {
          return TRUE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

}
