<?php

namespace Drupal\view_mode_page\PathProcessor;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Url;
use Drupal\view_mode_page\Repository\ViewmodepagePatternRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DynamicPathProcessor.
 *
 * @package Drupal\view_mode_page\PathProcessor
 */
class DynamicPathProcessor implements InboundPathProcessorInterface, ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * An alias manager for looking up the system path.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The viewmodepage pattern repository.
   *
   * @var \Drupal\view_mode_page\Repository\ViewmodepagePatternRepository
   */
  protected $patternRepository;

  /**
   * DynamicPathProcessor constructor.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The alias manager interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\view_mode_page\Repository\ViewmodepagePatternRepository $pattern_repository
   *   The viewmodepage pattern repository.
   */
  public function __construct(AliasManagerInterface $alias_manager, EntityTypeManagerInterface $entity_type_manager, ViewmodepagePatternRepository $pattern_repository) {
    $this->aliasManager      = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->patternRepository = $pattern_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    // DR - REMINDER: Empty cache after altering this code!
    $patterns = $this->patternRepository->findAll();

    /** @var \Drupal\view_mode_page\ViewmodepagePatternInterface $pattern */
    foreach ($patterns as $pattern) {
      if (preg_match($pattern->getPatternRegex(), $path, $matchesArray)) {
        $entityAlias = $matchesArray[1];
        $entityUri = $this->aliasManager->getPathByAlias('/' . $entityAlias);

        $url = Url::fromUri('internal:' . $entityUri);
        if ($url->isRouted()) {
          $routeParams = $url->getRouteParameters();
          if ($entityType = key($routeParams)) {
            $entityId = $routeParams[$entityType];
          }
        }

        if (!empty($entityType) && !empty($entityId) && $entityType == $pattern->getAliasType()->getDerivativeId()) {
          $entity = $this->entityTypeManager->getStorage($entityType)->load($entityId);

          if ($entity instanceof EntityInterface) {
            if ($pattern->applies($entity)) {
              $newPath = '/view_mode_page/' . $pattern->getViewMode() . '/' . $entityType . '/' . $entityId;
              return $newPath;
            }
          }
        }
      }
    }

    return $path;
  }

}
