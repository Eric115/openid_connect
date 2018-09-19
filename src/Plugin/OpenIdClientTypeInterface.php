<?php

namespace Drupal\openid_connect\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Interface for OpenID client types.
 */
interface OpenIdClientTypeInterface extends PluginInspectionInterface, PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Returns the OpenID authorization endpoint URL.
   *
   * @see http://openid.net/specs/openid-connect-core-1_0.html#AuthorizationEndpoint
   *
   * @return string
   *   Authorization endpoint URL.
   */
  public function getAuthorizationUrl();

  /**
   * Returns the OpenID token endpoint URL.
   *
   * @see http://openid.net/specs/openid-connect-core-1_0.html#TokenEndpoint
   *
   * @return string
   *   Token endpoint URL.
   */
  public function getTokenUrl();

  /**
   * Returns the resource owner url.
   *
   * @return string
   *   Resource owner url.
   */
  public function getResourceOwnerUrl();

  /**
   * Returns the OpenID userinfo endpoint URL.
   *
   * @see http://openid.net/specs/openid-connect-core-1_0.html#UserInfo
   *
   * @return string
   *   User info endpoint URL.
   */
  public function getUserInfoUrl();

  /**
   * Returns the client ID used for remote authentication.
   *
   * @return string
   *   Client ID.
   */
  public function getClientId();

  /**
   * Returns the client secret used for remote authentication.
   *
   * @return string
   *   The client secret.
   */
  public function getClientSecret();

  /**
   * Get a string of scope claims.
   *
   * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
   *
   * @return string
   *   Space delimited case sensitive list of ASCII scope values.
   */
  public function getScope();

  /**
   * Get the access, refresh and ID tokens.
   *
   * @param string $code
   *   The authentication code passed back from the remote provider.
   *
   * @return array|bool
   *   An array of tokens or FALSE on failure.
   */
  public function getTokens(string $code);

  /**
   * Redirects the user to the authorization endpoint.
   *
   * The authorization endpoint authenticates the user and returns them
   * to the redirect_uri specified previously with an authorization code
   * that can be exchanged for an access token.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A response object.
   */
  public function authorize();

  /**
   * Request and normalize user info from remote provider.
   *
   * @return mixed
   *   Userinfo from remote.
   */
  public function getUserInfo();

  /**
   * Get the internal redirect URL for a successful connection.
   *
   * @return \Drupal\Core\Url
   *   Url object for redirect.
   */
  public function getSuccessRedirect();

  /**
   * Get the internal redirect URL for a failed connection.
   *
   * @return \Drupal\Core\Url
   *   Url object for redirect.
   */
  public function getFailRedirect();

}
