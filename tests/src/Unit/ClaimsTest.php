<?php

namespace Drupal\Tests\openid_connect\Unit;

use Drupal\openid_connect\ClaimsManager;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for the ClaimsManager.
 *
 * @group openid_connect
 */
class ClaimsTest extends UnitTestCase {

  /**
   * Claims manager.
   *
   * @var \Drupal\openid_connect\ClaimsManager
   */
  protected $claims;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->claims = new ClaimsManager();
  }

  /**
   * Test that scopes are correctly generated based on a given set of claims.
   *
   * @dataProvider providerTestClaims
   */
  public function testGetScopes(array $claims, $expected) {
    $scopes = $this->claims->getScopes($claims);
    $this->assertEquals($scopes, $expected);
  }

  /**
   * Data provider for testGetScopes().
   *
   * @return array
   *   Data for testGetScopes as an array of claims.
   */
  public function providerTestClaims() {
    return [
      // Empty set of claims.
      [
        [],
        'openid email',
      ],
      // Normal set of claims with different scopes.
      [
        ['phone_number', 'zoneinfo'],
        'openid email phone profile',
      ],
      // Multiple claims with same scope.
      [
        ['birthdate', 'zoneinfo', 'locale', 'phone_number'],
        'openid email profile phone',
      ],
      // Non-existent claims.
      [
        ['birthdate', 'zoneinfo', 'fake_claim', 'phone_number'],
        'openid email profile phone',
      ],
    ];
  }

}
