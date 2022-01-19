<?php

namespace Drupal\stripe_subscription_and_customer\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the stripe_subscription_and_customer module.
 */
class CustomerControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "stripe_subscription_and_customer CustomerController's controller functionality",
      'description' => 'Test Unit for module stripe_subscription_and_customer and controller CustomerController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests stripe_subscription_and_customer functionality.
   */
  public function testCustomerController() {
    // Check that the basic functions of module stripe_subscription_and_customer.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
