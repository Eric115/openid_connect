<?php

namespace Drupal\openid_connect\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\openid_connect\ClaimsManagerInterface;
use Drupal\openid_connect\Oauth2ClientFactoryInterface;
use Drupal\openid_connect\StateToken;
use Drupal\openid_connect\TokenStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface as HttpClientInterface;

/**
 * Base class for open id client types.
 */
abstract class OpenIdClientTypeBase extends PluginBase implements OpenIdClientTypeInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Instance of claims manager.
   *
   * @var \Drupal\openid_connect\ClaimsManagerInterface
   */
  protected $claimsManager;

  /**
   * Instance of a http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Instance of token store.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $tokenStore;

  /**
   * Instance of StateToken.
   *
   * @var \Drupal\openid_connect\StateToken
   */
  protected $stateToken;

  /**
   * Instance of OpenIdConnectClient.
   *
   * @var \Drupal\openid_connect\OpenIdConnectClient
   */
  protected $openIdClient;

  /**
   * Instance of GenericProvider.
   *
   * @var \League\OAuth2\Client\Provider\GenericProvider
   */
  protected $oauth2Client;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClaimsManagerInterface $claims_manager, HttpClientInterface $http_client, TokenStoreFactory $token_store_factory, StateToken $state_token, Oauth2ClientFactoryInterface $oauth2_client_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->claimsManager = $claims_manager;
    $this->httpClient = $http_client;
    $this->tokenStore = $token_store_factory->createStore($this->getPluginId());
    $this->stateToken = $state_token;

    // Don't create client if this is a new plugin.
    if (!empty($configuration) && $this->configuration['client_id']) {
      $this->oauth2Client = $oauth2_client_factory->createClient([
        'clientId' => $this->configuration['client_id'],
        'clientSecret' => $this->configuration['client_secret'],
        'redirectUri' => static::getRedirectUri($plugin_id),
        'urlAuthorize' => $this->getAuthorizationUrl(),
        'urlAccessToken' => $this->getTokenUrl(),
        'urlResourceOwnerDetails' => $this->getResourceOwnerUrl(),
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('openid_connect.claims_manager'),
      $container->get('http_client'),
      $container->get('openid_connect.token_store_factory'),
      $container->get('openid_connect.state_token'),
      $container->get('openid_connect.oauth2_client_factory'),
      $container->get('openid_connect.connect_client')
    );
  }

  /**
   * Returns the redirect URI.
   *
   * @param string $openid_client
   *   Redirect uri for this client.
   *
   * @return string
   *   Redirect uri string.
   */
  public static function getRedirectUri(string $openid_client) {
    return $redirect_uri = Url::fromRoute('openid_connect.provider_response_controller', ['openid_client' => $openid_client], ['absolute' => TRUE])->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'client_id' => '',
      'client_secret' => '',
      'claims' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getClientId() {
    return $this->configuration['client_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getClientSecret() {
    return $this->configuration['client_secret'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#maxlength' => 255,
      '#default_value' => $this->configuration['client_id'],
      '#description' => $this->t('Client ID for the remote endpoint.'),
    ];
    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#maxlength' => 255,
      '#default_value' => $this->configuration['client_secret'],
      '#description' => $this->t('Client Secret for the remote endpoint.'),
    ];

    $defined_claims = $this->claimsManager->getClaims();

    $form['claims'] = [
      '#type' => 'details',
      '#title' => $this->t('Claims'),
      '#description' => $this->t('Selected which claims you would like to make against the remote provider. Email is always requested as a bare minimum. <br /> See <a href="http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims" target="_blank">the OpenID spec</a> for more info.'),
      '#open' => TRUE,
    ];

    $claims_config = $this->configuration['claims'];

    foreach ($defined_claims as $claim_id => $claim) {
      $form['claims'][$claim_id] = [
        '#type' => 'checkbox',
        '#title' => $claim['title'],
        '#description' => $claim['description'],
        '#default_value' => isset($claims_config[$claim_id]) ? $claims_config[$claim_id] : 0,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function getScope() {
    $claims = $this->configuration['claims'];
    $claims = is_array($claims) ? $claims : [];
    return $this->claimsManager->getScopes($claims);
  }

  /**
   * {@inheritdoc}
   */
  public function authorize() {
    // Inject our own state token using Drupal's Crypt class.
    $state = $this->stateToken->createToken('state');
    $auth_url = $this->oauth2Client->getAuthorizationUrl([
      'state' => $state,
      'scope' => $this->getScope(),
    ]);

    // Send user to provider's sign-in page.
    $response = new TrustedRedirectResponse($auth_url);
    $response->setMaxAge(0);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getTokens(string $code) {
    $access_token = $this->oauth2Client->getAccessToken('authorization_code', [
      'code' => $code,
    ]);

    $this->tokenStore->set('access', $access_token->getToken());
    $this->tokenStore->set('refresh', $access_token->getRefreshToken());
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfo() {
    if ($access_token = $this->tokenStore->get('access')) {
      $request_options = [
        'headers' => [
          'Authorization' => 'Bearer ' . $access_token,
        ],
      ];

      try {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $this->httpClient->get($this->getUserInfoUrl(), $request_options);
        $response_data = json_decode((string) $response->getBody(), TRUE);

        return $response_data;
      }
      catch (\Exception $e) {
        return FALSE;
      }
    }

    return FALSE;
  }

  /**
   * Get the resource owner details endpoint.
   *
   * @return string
   *   URL.
   */
  public function getResourceOwnerUrl() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getFailRedirect() {
    return Url::fromRoute('user.login');
  }

  /**
   * {@inheritdoc}
   */
  public function getSuccessRedirect() {
    return Url::fromRoute('user.page');
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}
