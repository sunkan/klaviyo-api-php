<?php

namespace Klaviyo\Tests\Model;

use Klaviyo\KlaviyoApi;
use Klaviyo\Model\ModelInterface;
use Klaviyo\Model\PersonReferenceModel;

class KlaviyoPersonReferenceTest extends KlaviyoBaseTest {

  protected $class = 'Klaviyo\Model\PersonReferenceModel';
  protected $configuration;

  public function setUp() {
    $this->configuration = [
      'object' => 'person',
      'id' => 'dqQnNW',
      'email' => 'george.washington@example.com',
    ];
  }

  public function assertModelMatchesConfiguration(ModelInterface $person, $configuration = array()) {
    if (empty($configuration)) {
      $configuration = $this->configuration;
    }

    $this->assertSame($configuration['object'], $person->getObjectType());
    $this->assertSame($configuration['id'], $person->getId());
    $this->assertSame($configuration['email'], $person->getEmail());
  }

}
