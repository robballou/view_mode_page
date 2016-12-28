<?php

namespace Drupal\view_mode_page\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ViewmodepagePatternRepository.
 *
 * @package Drupal\view_mode_page\Repository
 */
class ViewmodepagePatternRepository {
  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ViewmodepagePatternRepository constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Find all patterns.
   *
   * @return \Drupal\view_mode_page\ViewmodepagePatternInterface[]
   *   The viewmodepage pattern interface.
   */
  public function findAll() {
    static $patterns;

    if (!isset($patterns)) {
      $ids      = $this->entityTypeManager->getStorage('view_mode_page_pattern')->getQuery()->sort('weight')->execute();
      $patterns = $this->entityTypeManager->getStorage('view_mode_page_pattern')->loadMultiple($ids);
    }

    return $patterns;
  }

}
