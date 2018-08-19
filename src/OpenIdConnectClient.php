<?php

namespace Drupal\openid_connect;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
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

  /**
   * Set or edit parameters to be sent with authentication request.
   *
   * @param array $values
   *   An array of url-safe values, keyed by variable name.
   *
   * @return $this
   *   Instance of self.
   */
  public function setCustomAuthParameters(array $values) {
    $this->customAuthParams = $values;
    return $this;
  }

  /**
   * Set or edit parameters to be posted when requesting tokens.
   *
   * @param array $values
   *   An array of values, keyed by variable name.
   *
   * @return $this
   *   Instance of self.
   */
  public function setCustomTokenParams(array $values) {
    $this->customTokenParams = $values;
    return $this;
  }

  /**
   * Set the redirect URI which is sent to login provider during authentication.
   *
   * @param string $uri
   *   URI to redirect to after user has logged in and granted access.
   *
   * @return $this
   *   Instance of self.
   */
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
   *   Authentication code which can be swapped for an access token.
   * @param string $token_endpoint
   *
   * @return array
   *   An array of tokens: access, refresh and id.
   */
  public function requestTokens(string $code, string $token_endpoint) {
    $token_params['form_params'] = $this->customTokenParams + [
      'grant_type' => 'authorization_code',
      'code' => $code,
      'redirect_uri' => $this->redirectUri,
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
    ];

    $response = $this->httpClient->request('post', $token_endpoint, $token_params);
    $response_data = json_decode((string) $response->getBody(), TRUE);

    return [
      'access' => $response_data['access_token'],
      'refresh' => $response_data['refresh_token'],
      'id' => $response_data['id_token'],
    ];
  }

}
