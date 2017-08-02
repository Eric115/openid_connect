<?php

namespace Drupal\openid_connect;

/**
 * OpenId claims.
 */
interface ClaimsManagerInterface {

  /**
   * Returns OpenID Connect claims.
   *
   * @return array
   *   List of claims.
   */
  public function getClaims();

  /**
   * Returns scopes that have to be requested based on the provided claims.
   *
   * @param array $claims
   *   An array of claim keys.
   *
   * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
   *
   * @return string
   *   Space delimited case sensitive list of ASCII scope values.
   */
  public function getScopes(array $claims);

}
