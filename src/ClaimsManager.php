<?php

namespace Drupal\openid_connect;

/**
 * Standard OpenID claims for getting user info.
 *
 * @see http://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
 * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
 */
class ClaimsManager implements ClaimsManagerInterface {

  /**
   * The standard claims as defined in the OpenID spec.
   *
   * @var array
   */
  protected $claims = [
    'name' => [
      'scope' => 'profile',
      'title' => 'Name',
      'type' => 'string',
      'description' => 'Full name',
    ],
    'given_name' => [
      'scope' => 'profile',
      'title' => 'Given name',
      'type' => 'string',
      'description' => 'Given name(s) or first name(s)',
    ],
    'family_name' => [
      'scope' => 'profile',
      'title' => 'Family name',
      'type' => 'string',
      'description' => 'Surname(s) or last name(s)',
    ],
    'middle_name' => [
      'scope' => 'profile',
      'title' => 'Middle name',
      'type' => 'string',
      'description' => 'Middle name(s)',
    ],
    'nickname' => [
      'scope' => 'profile',
      'title' => 'Nickname',
      'type' => 'string',
      'description' => 'Casual name',
    ],
    'preferred_username' => [
      'scope' => 'profile',
      'title' => 'Preferred username',
      'type' => 'string',
      'description' => 'Shorthand name by which the End-User wishes to be referred to',
    ],
    'profile' => [
      'scope' => 'profile',
      'title' => 'Profile',
      'type' => 'string',
      'description' => 'Profile page URL',
    ],
    'picture' => [
      'scope' => 'profile',
      'title' => 'Picture',
      'type' => 'string',
      'description' => 'Profile picture URL',
    ],
    'website' => [
      'scope' => 'profile',
      'title' => 'Website',
      'type' => 'string',
      'description' => 'Web page or blog URL',
    ],
    'email' => [
      'scope' => 'email',
      'title' => 'Email',
      'type' => 'string',
      'description' => 'Preferred e-mail address',
    ],
    'email_verified' => [
      'scope' => 'email',
      'title' => 'Email verified',
      'type' => 'boolean',
      'description' => 'True if the e-mail address has been verified; otherwise false',
    ],
    'gender' => [
      'scope' => 'profile',
      'title' => 'Gender',
      'type' => 'string',
      'description' => 'Gender',
    ],
    'birthdate' => [
      'scope' => 'profile',
      'title' => 'Birthdate',
      'type' => 'string',
      'description' => 'Birthday',
    ],
    'zoneinfo' => [
      'scope' => 'profile',
      'title' => 'Zoneinfo',
      'type' => 'string',
      'description' => 'Time zone',
    ],
    'locale' => [
      'scope' => 'profile',
      'title' => 'Locale',
      'type' => 'string',
      'description' => 'Locale',
    ],
    'phone_number' => [
      'scope' => 'phone',
      'title' => 'Phone number',
      'type' => 'string',
      'description' => 'Preferred telephone number',
    ],
    'phone_number_verified' => [
      'scope' => 'phone',
      'title' => 'Phone number verified',
      'type' => 'boolean',
      'description' => 'True if the phone number has been verified; otherwise false',
    ],
    'address' => [
      'scope' => 'address',
      'title' => 'Address',
      'type' => 'json',
      'description' => 'Preferred postal address',
    ],
    'updated_at' => [
      'scope' => 'profile',
      'title' => 'Updated at',
      'type' => 'number',
      'description' => 'Time the information was last updated',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function getClaims() {
    return $this->claims;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopes(array $claims) {
    // Required scopes for authorizations and Drupal login.
    $scopes = ['openid', 'email'];

    // Guard statement.
    if (empty($claims)) {
      return implode(' ', $scopes);
    }

    $defined_claims = $this->getClaims();

    foreach ($claims as $claim) {
      if (isset($defined_claims[$claim]) && !in_array($defined_claims[$claim]['scope'], $scopes)) {
        $scopes[] = $defined_claims[$claim]['scope'];
      }
    }

    return implode(' ', $scopes);
  }

}
