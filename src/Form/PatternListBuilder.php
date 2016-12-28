<?php

namespace Drupal\view_mode_page\Form;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Viewmodepage pattern entities.
 */
class PatternListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'view_mode_page_pattern_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label']      = $this->t('Label');
    $header['pattern']    = $this->t('Pattern');
    $header['type']       = $this->t('Entity type');
    $header['view_mode']  = $this->t('View mode');
    $header['conditions'] = $this->t('Conditions');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\view_mode_page\ViewmodepagePatternInterface $entity */
    $row['label']                = $entity->label();
    $row['patern']['#markup']    = $entity->getPattern();
    $row['type']['#markup']      = $entity->getAliasType()->getLabel();
    $row['view_mode']['#markup'] = $entity->getViewModeLabel();
    $row['conditions']['#theme'] = 'item_list';
    foreach ($entity->getSelectionConditions() as $condition) {
      $row['conditions']['#items'][] = $condition->summary();
    }
    return $row + parent::buildRow($entity);
  }

}
