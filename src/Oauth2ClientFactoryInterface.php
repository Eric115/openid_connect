<?php

namespace Drupal\openid_connect;

/**
 * Defines an interface for Oauth2 clients.
 */
interface Oauth2ClientFactoryInterface {

  /**
   * Get a new instance of Oauth2 client with options set.
   *
   * @param array $options
   *   An array of options for client.
   * @param array $collaborators
   *   An array of $collaborators.
   *
   * @return object
   *   Instance of Oauth2 client.
   */
  public function createClient(array $options = [], array $collaborators = []);

}
