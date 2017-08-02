<?php

namespace Drupal\openid_connect\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\openid_connect\Form\LoginForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'OpenID Connect login' block.
 *
 * @Block(
 *  id = "openid_connect_login",
 *  admin_label = @Translation("OpenID Connect login"),
 * )
 */
class LoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Instance of form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->formBuilder->getForm(LoginForm::class);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

}
