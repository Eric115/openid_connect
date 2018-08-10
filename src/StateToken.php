<?php

namespace Drupal\openid_connect;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StateToken.
 *
 * @package Drupal\openid_connect
 */
class StateToken {

  /**
   * Instance of private temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Instance of session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Private temp store.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * {@inheritdoc}
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;
    $this->store = $this->tempStoreFactory->get('openid_connect');

    if ($this->currentUser->isAnonymous() && !isset($_SESSION['session_started'])) {
      $_SESSION['session_started'] = TRUE;
      $this->sessionManager->start();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
  }

  /**
   * Creates a state token and stores it in the session for later validation.
   *
   * @return string
   *   A state token that later can be validated to prevent request forgery.
   */
  public function createToken() {
    // Generate a secure state token.
    $state_token = Crypt::randomBytesBase64();
    $this->store->set('state_token', $state_token);
    return $state_token;
  }

  /**
   * Delete the state token stored in the session.
   */
  public function destroyToken() {
    $this->store->delete('state_token');
  }

  /**
   * Confirms anti-forgery state token.
   *
   * @param string $state_token
   *   The state token to be checked.
   *
   * @return bool
   *   True if the passed state token matches the previously created one.
   */
  public function confirm($state_token) {
    $session_token = $this->store->get('state_token');
    return !empty($session_token) && $state_token === $session_token;
  }

}
