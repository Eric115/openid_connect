<?php

namespace Drupal\openid_connect\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\openid_connect\StateToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Check that there is a valid state token in the request.
 */
class ValidStateTokenAccessCheck implements AccessInterface, ContainerInjectionInterface {

  /**
   * State Token service.
   *
   * @var \Drupal\openid_connect\StateToken
   */
  protected $stateToken;

  /**
   * ValidStateTokenAccessCheck constructor.
   *
   * @param \Drupal\openid_connect\StateToken $state_token
   *   StateToken service.
   */
  public function __construct(StateToken $state_token) {
    $this->stateToken = $state_token;
  }

  /**
   * Determine if access to this controller is allowed or not.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   *   Access allowed or forbidden.
   */
  public function access(Request $request) {
    // Token from request.
    $token = $request->query->get('state');

    if (!empty($token) && $this->stateToken->confirm('state', $token)) {
      // Delete the token so it can't be used again.
      $this->stateToken->destroyToken('state');
      return AccessResult::allowed()->setCacheMaxAge(0);
    }

    return AccessResult::forbidden()->setCacheMaxAge(0);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openid_connect.state_token')
    );
  }

}
