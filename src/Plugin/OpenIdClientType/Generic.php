<?php

namespace Drupal\openid_connect\Plugin\OpenIdClientType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\openid_connect\Plugin\OpenIdClientTypeBase;

/**
 * A generic OpenID login client.
 *
 * @OpenIdClientType(
 *   id = "generic",
 *   label = @Translation("Generic")
 * )
 */
class Generic extends OpenIdClientTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config_form = parent::buildConfigurationForm($form, $form_state);

    $config_form['authorization_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authorization Endpoint'),
      '#maxlength' => 255,
      '#default_value' => $this->getAuthorizationUrl(),
      '#description' => $this->t('Client authorization endpoint URL for OpenID.'),
      '#required' => TRUE,
    ];
    $config_form['token_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token Endpoint'),
      '#maxlength' => 255,
      '#default_value' => $this->getTokenUrl(),
      '#description' => $this->t('Client token endpoint URL for OpenID.'),
      '#required' => TRUE,
    ];
    $config_form['userinfo_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Userinfo Endpoint'),
      '#maxlength' => 255,
      '#default_value' => $this->getUserInfoUrl(),
      '#description' => $this->t('Client userinfo endpoint URL for OpenID.'),
      '#required' => TRUE,
    ];

    return $config_form;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorizationUrl() {
    return $this->configuration['authorization_endpoint'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenUrl() {
    return $this->configuration['token_endpoint'];
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfoUrl() {
    return $this->configuration['userinfo_endpoint'];
  }

}
