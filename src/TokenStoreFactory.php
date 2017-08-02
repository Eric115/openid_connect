<?php

namespace Drupal\openid_connect;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wrapper around PrivateTempStoreFactory to create a token store.
 *
 * @package Drupal\openid_connect
 */
class TokenStoreFactory {

  /**
   * Temp store factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Session manager.
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
   * TokenStore constructor.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   Temp store factory.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;

    if ($this->currentUser->isAnonymous() && !$this->sessionManager->isStarted()) {
      $this->sessionManager->start();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
  }

  /**
   * Create a token store.
   *
   * @param string $collection
   *   Collection ID.
   *
   * @return \Drupal\user\PrivateTempStore
   *   Token store.
   */
  public function createStore($collection) {
    // Prepend module name to collection.
    $this->store = $this->tempStoreFactory->get("openid_connect_$collection");
    return $this->store;
  }

  /**
   * Get the previously created token store, if it exists.
   *
   * @return \Drupal\user\PrivateTempStore|null
   *   Store if it exists or NULL.
   */
  public function getStore() {
    return isset($this->store) ? $this->store : NULL;
  }

}
