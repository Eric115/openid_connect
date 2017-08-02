<?php

namespace Drupal\openid_connect\Plugin\OpenIdClientType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\openid_connect\Plugin\OpenIdClientTypeBase;

/**
 * Microsoft client.
 *
 * @OpenIdClientType(
 *   id = "microsoft",
 *   label = @Translation("Microsoft")
 * )
 */
class Microsoft extends OpenIdClientTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'tenant_id' => 'common',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['tenant_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tenant ID'),
      '#maxlength' => 255,
      '#default_value' => $this->configuration['tenant_id'],
      '#description' => $this->t('Microsoft tenant ID.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorizationUrl() {
    return 'https://login.microsoftonline.com/' . $this->configuration['tenant_id'] . '/oauth2/authorize';
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenUrl() {
    return 'https://login.microsoftonline.com/' . $this->configuration['tenant_id'] . '/oauth2/token';
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfoUrl() {
    return 'https://login.microsoftonline.com/' . $this->configuration['tenant_id'] . '/openid/userinfo';
  }

}
