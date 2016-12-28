<?php

namespace Drupal\view_mode_page;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface for defining Viewmodepage pattern entities.
 */
interface ViewmodepagePatternInterface extends ConfigEntityInterface {

  /**
   * Get the pattern used during path processing.
   *
   * @return string
   *   Returns the pattern used during path processing.
   */
  public function getPattern();

  /**
   * Set the pattern regex to use during path processing.
   *
   * @return string
   *   Returns the pattern regex to use during path processing.
   */
  public function getPatternRegex();

  /**
   * Set the pattern to use during path processing.
   *
   * @param string $pattern
   *   The pattern.
   *
   * @return $this
   *   Returns the pattern to use during path processing.
   */
  public function setPattern($pattern);

  /**
   * Gets the type of view_mode.
   *
   * @return string
   *   Returns the type of view_mode.
   */
  public function getViewMode();

  /**
   * Gets the label of view_mode.
   *
   * @return string
   *   Returns the label of view_mode.
   */
  public function getViewModeLabel();

  /**
   * Gets the type of this pattern.
   *
   * @return string
   *   Returns the type of this pattern.
   */
  public function getType();

  /**
   * Gets the alias type interface.
   *
   * @return \Drupal\view_mode_page\AliasTypeInterface
   *   Returns the alias type interface.
   */
  public function getAliasType();

  /**
   * Gets the weight of this pattern (compared to other patterns of this type).
   *
   * @return int
   *   Returns the weight
   */
  public function getWeight();

  /**
   * Sets the weight of this pattern (compared to other patterns of this type).
   *
   * @param int $weight
   *   The weight of the variant.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Returns the contexts of this pattern.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   Return the context interface.
   */
  public function getContexts();

  /**
   * Returns whether a relationship exists.
   *
   * @param string $token
   *   Relationship identifier.
   *
   * @return bool
   *   TRUE if the relationship exists, FALSE otherwise.
   */
  public function hasRelationship($token);

  /**
   * Adds a relationship.
   *
   * The relationship will not be changed if it already exists.
   *
   * @param string $token
   *   Relationship identifier.
   * @param string|null $label
   *   (optional) A label, will use the label of the referenced context if not
   *   provided.
   *
   * @return $this
   */
  public function addRelationship($token, $label = NULL);

  /**
   * Replaces a relationship.
   *
   * Only already existing relationships are updated.
   *
   * @param string $token
   *   Relationship identifier.
   * @param string|null $label
   *   (optional) A label, will use the label of the referenced context if not
   *   provided.
   *
   * @return $this
   */
  public function replaceRelationship($token, $label);

  /**
   * Removes a relationship.
   *
   * @param string $token
   *   Relationship identifier.
   *
   * @return $this
   */
  public function removeRelationship($token);

  /**
   * Returns a list of relationships.
   *
   * @return array[]
   *   Keys are context tokens, and values are arrays with the following keys:
   *   - label (string|null, optional): The human-readable label of this
   *     relationship.
   */
  public function getRelationships();

  /**
   * Gets the selection condition collection.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   Returns a array/collection of condition objects
   */
  public function getSelectionConditions();

  /**
   * Adds selection criteria.
   *
   * @param array $configuration
   *   Configuration of the selection criteria.
   *
   * @return string
   *   The condition id of the new criteria.
   */
  public function addSelectionCondition(array $configuration);

  /**
   * Gets selection criteria by condition id.
   *
   * @param string $condition_id
   *   The id of the condition.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   Returns the condition interface
   */
  public function getSelectionCondition($condition_id);

  /**
   * Removes selection criteria by condition id.
   *
   * @param string $condition_id
   *   The id of the condition.
   *
   * @return $this
   *   Returns the current object
   */
  public function removeSelectionCondition($condition_id);

  /**
   * Gets the selection logic used by the criteria (ie. "and" or "or").
   *
   * @return string
   *   Either "and" or "or"; represents how the selection criteria are combined.
   */
  public function getSelectionLogic();

  /**
   * Determines if this pattern can apply a given object.
   *
   * @param EntityInterface $entity
   *   The entity used to determine if this plugin can apply.
   *
   * @return bool
   *   Returns true or false
   */
  public function applies(EntityInterface $entity);

}
