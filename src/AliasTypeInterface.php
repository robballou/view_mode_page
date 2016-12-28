<?php

namespace Drupal\view_mode_page;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;

/**
 * Provides an interface for view_mode_page alias types.
 */
interface AliasTypeInterface extends ContextAwarePluginInterface, DerivativeInspectionInterface {

  /**
   * Get the label.
   *
   * @return string
   *   The label.
   */
  public function getLabel();

  /**
   * Get the token types.
   *
   * @return string[]
   *   The token types.
   */
  public function getTokenTypes();

  /**
   * Determines if this plugin type can apply a given object.
   *
   * @param object $object
   *   The object used to determine if this plugin can apply.
   *
   * @return bool
   *   Whether this plugin applies to the given object.
   */
  public function applies($object);

}
