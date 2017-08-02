<?php

namespace Drupal\openid_connect;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface for defining a new OpenID Connect client.
 */
interface OpenIdClientInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * Calls the authorize method on the associated clientType plugin.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A response object.
   */
  public function authorize();

  /**
   * Calls the getTokens method on the associated clientType plugin.
   *
   * @param string $auth_code
   *   The authentication code passed back from the remote provider.
   *
   * @return array|bool
   *   An array of tokens or FALSE on failure.
   */
  public function getTokens($auth_code);

  /**
   * Calls the getUserInfo method on associated clientType plugin.
   */
  public function getUserInfo();

  /**
   * Get the internal url to redirect users to after negotiations with remote.
   *
   * @return string
   *   The url.
   */
  public function getSuccessRedirectUrl();

  /**
   * Get the internal url to redirect users to after negotiations with remote.
   *
   * @return string
   *   The url.
   */
  public function getFailRedirectUrl();

}
