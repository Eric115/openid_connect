<?php

namespace Drupal\openid_connect\Entity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\openid_connect\OpenIdClientInterface;

/**
 * Entity controller.
 */
class OpenIdClientController extends ControllerBase {

  /**
   * Perform an operation on an OpenId Client and reload the listing screen.
   *
   * @param \Drupal\openid_connect\OpenIdClientInterface $openid_client
   *   The OpenID client entity.
   * @param string $op
   *   The operation. E.g. 'enable'.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the client list page.
   */
  public function performOperation(OpenIdClientInterface $openid_client, $op) {
    $openid_client->$op()->save();

    if ($op === 'enable') {
      drupal_set_message($this->t('The %label client has been enabled.', ['%label' => $openid_client->label()]));
    }
    elseif ($op === 'disable') {
      drupal_set_message($this->t('The %label client has been disabled.', ['%label' => $openid_client->label()]));
    }

    // Return to the listing page.
    $url = Url::fromRoute('openid_connect.client_list');
    return $this->redirect($url->getRouteName(), $url->getRouteParameters(), $url->getOptions());
  }

}
