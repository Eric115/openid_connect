<?php

namespace Drupal\openid_connect;

use Drupal\Core\Config\Entity\ConfigEntityStorage;

/**
 * An extension of ConfigEntityStorage for OpenIdClient entities.
 */
class OpenIdClientStorage extends ConfigEntityStorage {

  /**
   * Get all enabled OpenID clients.
   *
   * @return array
   *   An array of enabled clients keyed by client ID.
   */
  public function getEnabledClients() {
    return $this->loadByProperties(['status' => 1]);
  }

}
