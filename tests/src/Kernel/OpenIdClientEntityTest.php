<?php

namespace Drupal\Tests\openid_connect\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\openid_connect\Entity\OpenIdClient;
use Drupal\openid_connect\Plugin\OpenIdClientType\Generic;

/**
 * Client Type kernel tests.
 *
 * @group openid_connect
 */
class OpenIdClientEntityTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'openid_connect',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('openid_client');
  }

  /**
   * Test the client entity.
   */
  public function testClientEntity() {
    $client_entity = OpenIdClient::create([
      'type' => 'generic',
      'id' => 'fancy',
      'label' => 'Fancy Open ID server',
      'type_settings' => [
        'client_id' => 'abc123',
        'client_secret' => '123abc',
        'claims' => [
          'phone' => 1,
        ],
      ],
    ]);
    $client_entity->save();
    $this->assertInstanceOf(Generic::class, $client_entity->getClientType());
  }

}
