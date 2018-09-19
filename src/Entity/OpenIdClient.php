<?php

namespace Drupal\openid_connect\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\openid_connect\OpenIdClientInterface;

/**
 * Defines an OpenID Connect client entity class.
 *
 * @ConfigEntityType(
 *   id = "openid_client",
 *   label = @Translation("OpenID Client"),
 *   fieldable = FALSE,
 *   handlers = {
 *     "storage" = "Drupal\openid_connect\OpenIdClientStorage",
 *     "list_builder" = "Drupal\openid_connect\OpenIdClientListBuilder",
 *     "form" = {
 *      "add" = "Drupal\openid_connect\Form\OpenIdClientForm",
 *      "edit" = "Drupal\openid_connect\Form\OpenIdClientForm",
 *      "delete" = "Drupal\openid_connect\Form\OpenIdClientDeleteForm"
 *     }
 *   },
 *   config_prefix = "openid_client",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "status" = "status"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/openid_client/{machine_name}/edit",
 *     "delete-form" = "/admin/structure/openid_client/{machine_name}/delete",
 *     "enable" = "/admin/structure/openid_client/{machine_name}/enable",
 *     "disable" = "/admin/structure/openid_client/{machine_name}/disable"
 *   }
 * )
 */
class OpenIdClient extends ConfigEntityBase implements OpenIdClientInterface {

  /**
   * The client plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The client name.
   *
   * @var string
   */
  public $name;

  /**
   * Client type.
   *
   * @var string
   */
  public $type;

  /**
   * Settings specific to the client type.
   *
   * @var array
   */
  public $type_settings = [];

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'type_settings' => $this->getTypePluginCollection(),
    ];
  }

  /**
   * Get the plugin collection.
   *
   * @returns \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The plugin collection.
   */
  protected function getTypePluginCollection() {
    if ($this->type) {
      return new DefaultSingleLazyPluginCollection(\Drupal::service('openid_connect.openid_client_type_manager'), $this->type, $this->type_settings);
    }

    return NULL;
  }

  /**
   * Get the client type.
   *
   * @return \Drupal\openid_connect\Plugin\OpenIdClientTypeInterface
   *   Instance of client for give type.
   */
  public function getClientType() {
    $collection = $this->getTypePluginCollection();
    return $collection->get($this->type);
  }

  /**
   * {@inheritdoc}
   */
  public function authorize() {
    return $this->getClientType()->authorize();
  }

  /**
   * {@inheritdoc}
   */
  public function getTokens($auth_code) {
    return $this->getClientType()->getTokens($auth_code);
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfo() {
    return $this->getClientType()->getUserInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function getSuccessRedirectUrl() {
    return $this->getClientType()->getSuccessRedirect()->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function getFailRedirectUrl() {
    return $this->getClientType()->getFailRedirect()->toString();
  }

}
