<?php

namespace Drupal\openid_connect;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a way to create and access random tokens in a private temp store.
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
   * Creates a random token and stores it in a private temp store.
   *
   * @param string $name
   *   Token name.
   *
   * @return string
   * A secure token that later can be validated to prevent request forgery.
   */
  public function createToken(string $name) {
    // Generate a secure state token.
    $token = Crypt::randomBytesBase64();
    $this->store->set($name, $token);
    // Make sure the session is saved as we are likely sending a redirect next.
    $this->sessionManager->save();
    return $token;
  }

  /**
   * Delete a token from the session.
   *
   * @param string $name
   *   Token name.
   */
  public function destroyToken(string $name) {
    $this->store->delete($name);
  }

  /**
   * Validate a token in the session matches a given value in constant time.
   *
   * Deletes the token from the session after comparing.
   *
   * @param string $name
   *   The name (key) of the token stored in the session (private temp store).
   * @param string $comparison
   *   The string to compare it against.
   *
   * @return bool
   *   TRUE if the passed token matches the value in session.
   */
  public function confirm(string $name, string $comparison) {
    $result = FALSE;

    if ($session_value = $this->store->get($name)) {
      $result = Crypt::hashEquals($session_value, $comparison);
    }

    // Tokens should only ever be used once, so destroy after comparison.
    $this->store->delete($name);

    // The hash_equals function returns FALSE immediately
    // if the strings are different length, so if the value is not set in the
    // session, there is no need to call hashEquals().
    return $result;
  }

}
