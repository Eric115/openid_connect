<?php

namespace Drupal\openid_connect;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * An openID Connect client for authentication and fetching profile info.
 */
class OpenIdConnectClient {

  /**
   * Client ID.
   *
   * @var string
   */
  protected $clientId;

  /**
   * Client name.
   *
   * @var string
   */
  protected $clientName;

  /**
   * Client secret.
   *
   * @var string
   */
  protected $clientSecret;

  /**
   * Instance of ClientInterface.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Instance of session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Custom authentication parameters.
   *
   * @var array
   */
  protected $customAuthParams = [];

  /**
   * Return redirect uri after user login.
   *
   * @var string
   */
  protected $redirectUri;

  /**
   * @var \Drupal\openid_connect\StateToken
   */
  protected $stateToken;

  /**
   * @var array
   */
  protected $customTokenParams;

  /**
   * Creates an instance of OpenIdConnectClient.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client, or NULl for default Guzzle client.
   * @param \Drupal\openid_connect\StateToken $state_token
   */
  public function __construct(ClientInterface $http_client, StateToken $state_token) {
    $this->httpClient = $http_client;
    $this->stateToken = $state_token;
  }

  public function setCustomAuthParameter(string $name, string $value) {
    $this->customAuthParams[$name] = $value;
    return $this;
  }

  public function setCustomAuthParameters(array $values) {
    $this->customAuthParams = $values;
    return $this;
  }

  public function setCustomTokenParams(array $values) {
    $this->customTokenParams = $values;
    return $this;
  }

  public function setRedirectUri(string $uri) {
    $this->redirectUri = $uri;
    return $this;
  }

  /**
   * Set the client ID.
   *
   * @param string $id
   *   Client ID.
   *
   * @return $this
   *   Instance of self.
   */
  public function setClientId(string $id) {
    $this->clientId = $id;
    return $this;
  }

  /**
   * Set the client secret.
   *
   * @param string $secret
   *   Client secret.
   *
   * @return $this
   *   Instance of self.
   */
  public function setClientSecret(string $secret) {
    $this->clientSecret = $secret;
    return $this;
  }

  /**
   * Send user to authentication portal.
   *
   * @param string $auth_url
   *   The URL to send authenticating clients to.
   * @param string $redirect_uri
   *   The return URL from the authentication portal.
   * @param array $custom_auth_params
   *   Custom authentication parameters to add to the auth query.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   Redirect response.
   */
  public function requestAuthorization(string $auth_url, string $redirect_uri, array $custom_auth_params = []) {
    $state = $this->stateToken->createToken('state');
    $nonce = $this->stateToken->createToken('nonce');

    $auth_parameters['query'] = $this->customAuthParams + [
      'response_type' => 'code',
      'redirect_uri' => $this->redirectUri,
      'client_id' => $this->clientId,
      'nonce' => $nonce,
      'state' => $state,
      'scope' => 'openid',
    ];

    $auth_url = Url::fromUri($auth_url, $auth_parameters)->toString(TRUE);

    $response = new TrustedRedirectResponse($auth_url);
    $response->addCacheableDependency($auth_url)
      ->addCacheableDependency($redirect_uri);

    return $response;
  }

  /**
   * Request ID, Access and Refresh tokens.
   *
   * @param string $code
   *   Authentication code which can be swapped for an access token
   * @param string $token_endpoint
   */
  public function requestTokens(string $code, string $token_endpoint) {
    $token_params['form_params'] = $this->customTokenParams + [
      'grant_type' => 'authorization_code',
      'code' => $code,
      'redirect_uri' => $this->redirectUri,
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
    ];
  }

}
