<?php

namespace Drupal\openid_connect\Form;

use Drupal\Core\Form\SubformState;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Entity form for OpenIdClient.
 */
class OpenIdClientForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\openid_connect\Entity\OpenIdClient $client */
    $client = $this->entity;

    if ($this->operation === 'edit') {
      $form['#title'] = $this->t('Edit OpenID Connect Client: @name', ['@name' => $client->name]);
    }

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $client->name,
      '#description' => $this->t('OpenId Client Name'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#default_value' => $client->id,
      '#disabled' => !$client->isNew(),
      '#machine_name' => [
        'source' => ['name'],
        'exists' => 'openid_connect_client_load',
      ],
    ];

    /** @var \Drupal\openid_connect\OpenIdClientTypeManager $manager */
    $manager = \Drupal::service('openid_connect.openid_client_type_manager');

    if ($client->isNew()) {
      $client_types = array_map(function ($plugin_definition) {
        return $plugin_definition['label'];
      }, $manager->getDefinitions());
      $form['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Provider Type'),
        '#required' => TRUE,
        '#options' => $client_types,
      ];
    }
    else {
      /** @var \Drupal\openid_connect\Plugin\OpenIdClientTypeInterface $client_type */
      $client_type = $client->getClientType();

      $form['type_settings'] = [
        '#tree' => TRUE,
      ];
      $subform_state = SubformState::createForSubform($form['type_settings'], $form, $form_state);
      $form['type_settings'] += $client_type->buildConfigurationForm($form['type_settings'], $subform_state);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\openid_connect\Entity\OpenIdClient $entity */
    $entity = $this->entity;
    if (!$entity->isNew()) {
      $subform_state = SubformState::createForSubform($form['type_settings'], $form, $form_state);
      $entity->getClientType()->validateConfigurationForm($form['type_settings'], $subform_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\openid_connect\Entity\OpenIdClient $client */
    $client = $this->entity;

    // Only attempt to save these if the entity already has a type selected.
    if (!$client->isNew()) {
      $client_type = $client->getClientType();
      $subform_state = SubformState::createForSubform($form['type_settings'], $form, $form_state);
      $client_type->submitConfigurationForm($form['type_settings'], $subform_state);

      $client->set('label', trim($client->label()));
      $client->set('authorization_endpoint', $form_state->getValue('authorization_endpoint'));
      $client->set('token_endpoint', $form_state->getValue('token_endpoint'));
      $client->set('userinfo_endpoint', $form_state->getValue('userinfo_endpoint'));
      $client->set('type_settings', $form_state->getValue('type_settings'));
      $client->set('claims', $form_state->getValue('claims'));
    }

    $status = $client->save();

    $edit_link = $client->link($this->t('Edit'));
    $action = $status == SAVED_UPDATED ? 'updated' : 'added';

    // Add a message with the action.
    drupal_set_message($this->t('OpenID client %label has been %action.', ['%label' => $client->label(), '%action' => $action]));
    $this->logger('openid_client')->notice($this->t('OpenID client %label has been %action.', ['%label' => $client->label(), '%action' => $action]), ['link' => $edit_link]);
    $form_state->setRedirect('openid_connect.client_list');
  }

}
