<?php

namespace Drupal\openid_connect;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\openid_connect\Annotation\OpenIdClientType;
use Drupal\openid_connect\Plugin\OpenIdClientTypeInterface;

/**
 * A manager for OpenIdClientType plugins.
 */
class OpenIdClientTypeManager extends DefaultPluginManager {

  /**
   * Creates and instance of OpenIdClientTypeManager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/OpenIdClientType', $namespaces, $module_handler, OpenIdClientTypeInterface::class, OpenIdClientType::class);
    $this->alterInfo('openid_client_type');
    $this->setCacheBackend($cache_backend, 'openid_client_type');
  }

}
