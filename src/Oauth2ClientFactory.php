<?php

namespace Drupal\openid_connect;

use League\OAuth2\Client\Provider\GenericProvider;

/**
 * Provides a more generic way to get League's GenericProvider.
 */
class Oauth2ClientFactory implements Oauth2ClientFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createClient(array $options = [], array $collaborators = []) {
    return new GenericProvider($options, $collaborators);
  }

}
