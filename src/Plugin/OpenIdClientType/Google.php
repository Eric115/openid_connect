<?php

namespace Drupal\openid_connect\Plugin\OpenIdClientType;

use Drupal\openid_connect\Plugin\OpenIdClientTypeBase;

/**
 * Google OpenID client.
 *
 * @OpenIdClientType(
 *   id = "google",
 *   label = @Translation("Google")
 * )
 */
class Google extends OpenIdClientTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getAuthorizationUrl() {
    return 'https://accounts.google.com/o/oauth2/auth';
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenUrl() {
    return 'https://accounts.google.com/o/oauth2/token';
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfoUrl() {
    return 'https://www.googleapis.com/plus/v1/people/me/openIdConnect';
  }

}
