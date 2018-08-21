<?php

namespace Drupal\openid_connect\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openid_connect\Entity\OpenIdClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Login form for OpenID clients.
 */
class LoginForm extends FormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity storage.
   *
   * @var \Drupal\openid_connect\OpenIdClientStorage
   */
  protected $entityStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityStorage = $this->entityTypeManager->getStorage('openid_client');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openid_connect_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $clients = $this->entityStorage->getEnabledClients();

    /** @var \Drupal\openid_connect\Entity\OpenIdClient $client */
    foreach ($clients as $id => $client) {
      $form['openid_connect_client_' . $id . '_login'] = [
        '#type' => 'submit',
        '#value' => t('Log in with @client_title', [
          '@client_title' => $client->label(),
        ]),
        '#name' => $id,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $client_id = $form_state->getTriggeringElement()['#name'];
    /** @var \Drupal\openid_connect\Entity\OpenIdClient $client */
    $client = OpenIdClient::load($client_id);

    if (!$client) {
      drupal_set_message($this->t('Unable to load OpenID client with ID: %id', ['%id' => $client_id]), 'error');
      return;
    }

    // Authenticate against remote and set form response.
    $response = $client->authorize();
    $form_state->setResponse($response);
  }

}
