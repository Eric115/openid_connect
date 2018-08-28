<?php

namespace Drupal\openid_connect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\openid_connect\Entity\OpenIdClient;
use Drupal\openid_connect\TokenStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for handling login response from Providers.
 */
class ProviderResponseController extends ControllerBase {

  /**
   * Errors related to users not granting claims.
   */
  const CLAIM_ERRORS = [
    'interaction_required',
    'login_required',
    'account_selection_required',
    'consent_required',
  ];

  /**
   * Instance of token store factory.
   *
   * @var \Drupal\openid_connect\TokenStoreFactory
   */
  protected $tokenStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(TokenStoreFactory $token_store_factory) {
    $this->tokenStoreFactory = $token_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openid_connect.token_store_factory')
    );
  }

  /**
   * Check the response is valid and then pass to authentication.
   *
   * @param \Drupal\openid_connect\Entity\OpenIdClient $openid_client
   *   The OpenIDClient entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The route object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect.
   */
  public function validateResponse(OpenIdClient $openid_client, Request $request) {
    $query = $request->query;
    $error = $query->get('error');
    $success_url = $openid_client->getSuccessRedirectUrl();
    $failure_url = $openid_client->getFailRedirectUrl();

    // If the client is invalid or there is no auth code in the response
    // it's as if this page wasn't visited in the login workflow.
    if (empty($openid_client) || (!$error && !$query->get('code'))) {
      throw new NotFoundHttpException();
    }

    // If there is an error in the response, or getting access tokens fails.
    if ($error || !$this->authenticate($openid_client, $request)) {
      if (in_array($error, self::CLAIM_ERRORS)) {
        // If we have any one of the above errors, it means that the user hasn't
        // granted the authorization for the claims.
        drupal_set_message($this->t('Logging in with @provider has been cancelled.', ['@provider' => $openid_client->label()]), 'warning');
      }
      else {
        // Any other error should be logged. E.g. invalid scope.
        $variables = [
          '@error' => $error,
          '@details' => $query->get('error_description') ? $query->get('error_description') : $this->t('Unknown error.'),
        ];
        $message = 'Authorization failed: @error. Details: @details';
        $this->loggerFactory->get('openid_connect_' . $openid_client->label())->error($message, $variables);

        drupal_set_message($this->t('Could not authenticate with @provider.', $openid_client->label()), 'error');
      }

      return new RedirectResponse($failure_url);
    }

    return new RedirectResponse($success_url);
  }

  /**
   * Use the authentication code to attempt to get an access token.
   *
   * @param \Drupal\openid_connect\Entity\OpenIdClient $openid_client
   *   The OpenIDClient entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The route object.
   *
   * @return bool
   *   TRUE if getting the access token succeeds, FALSE on failure.
   */
  public function authenticate(OpenIdClient $openid_client, Request $request) {
    // Get the authentication code which can be swapped for an access token.
    $code = $request->query->get('code', FALSE);
    $openid_client->getTokens($code);

    // @TODO: Change this to use Events.
    $user_info = $openid_client->getUserInfo();
    $this->moduleHandler()->invokeAll('openid_user_authenticated', [$user_info]);
    return TRUE;
  }

}
