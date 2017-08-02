<?php

namespace Drupal\openid_connect\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation class for open id client types.
 *
 * @Annotation
 */
class OpenIdClientType extends Plugin {

  /**
   * Client Type ID.
   *
   * @var string
   */
  public $id;

  /**
   * Client Type label (human-readable).
   *
   * @var string
   */
  public $label;

}
