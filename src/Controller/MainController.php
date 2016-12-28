<?php

namespace Drupal\view_mode_page\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\AccessAwareRouterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class MainController.
 *
 * @package Drupal\view_mode_page\Controller
 */
class MainController extends ControllerBase {
  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The http kernel interface.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The access aware router interface.
   *
   * @var \Drupal\Core\Routing\AccessAwareRouterInterface
   */
  protected $router;

  /**
   * MainController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The http kernel interface.
   * @param \Drupal\Core\Routing\AccessAwareRouterInterface $router
   *   The access aware router interface.
   */
  public function __construct(RequestStack $request_stack, HttpKernelInterface $http_kernel, AccessAwareRouterInterface $router) {
    $this->requestStack = $request_stack;
    $this->httpKernel   = $http_kernel;
    $this->router       = $router;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('http_kernel'),
      $container->get('router')
    );
  }

  /**
   * Display a entity in a given view mode.
   *
   * @param string $view_mode
   *   The view mode.
   * @param string $entity_type
   *   The entity type.
   * @param mixed $entity_id
   *   The entity id.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The Symfony response.
   */
  public function displayEntity($view_mode, $entity_type, $entity_id) {
    $entity     = $this->entityTypeManager()->getStorage($entity_type)->load($entity_id);
    $entity_url = $entity->toUrl();

    // Response method as used in
    // core/lib/Drupal/Core/EventSubscriber/DefaultExceptionHtmlSubscriber.php:122
    // but with access aware router.
    $request     = $this->requestStack->getCurrentRequest();
    $sub_request = clone $request;
    $sub_request->attributes->add($this->router->match('/' . $entity_url->getInternalPath()));
    $sub_request->attributes->add(['view_mode' => $view_mode]);
    $response = $this->httpKernel->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
    return $response;
  }

}
