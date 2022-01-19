<?php

namespace Drupal\stripe_subscription_and_customer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\key\KeyRepositoryInterface;
use Psr\Log\LoggerInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Stripe\Stripe;
/**
 * Class StripeapiService.
 */
class StripeapiService implements StripeapiServiceInterface {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The Key Repository.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $key;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, KeyRepositoryInterface $key) {
    $this->config = $config_factory->get('stripe_api.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->key = $key;
    Stripe::setApiKey($this->getApiKey());
    $this->overrideApiVersion();
    $this->key_pub = \Drupal::service('stripe_api.stripe_api')->getApiKey();
  }
/**
   *
   */
  public function getMode() {
    $mode = $this->config->get('mode');

    if (!$mode) {
      $mode = 'test';
    }

    return $mode;
  }

  /**
   *
   */
  public function getApiKey() {
    $config_key = $this->getMode() . '_secret_key';
    $key_id = $this->config->get($config_key);
    if ($key_id) {
      $key_entity = $this->key->getKey($key_id);
      if ($key_entity) {
        return $key_entity->getKeyValue();
      }

    }

    return NULL;
  }

  /**
   *
   */
  public function getPubKey() {
    $config_key = $this->getMode() . '_public_key';
    $key_id = $this->config->get($config_key);
    if ($key_id) {
      $key_entity = $this->key->getKey($key_id);
      if ($key_entity) {
        return $key_entity->getKeyValue();
      }
    }

    return NULL;
  }

  /**
   * Overrides API version.
   */
  public function overrideApiVersion() {
    if ($this->config->get('api_version') === 'custom') {
      Stripe::setApiVersion($this->config->get('api_version_custom'));
    }
  }
  
  public function getCustomersID($user_id) {
    $account = User::load($user_id);
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $customer = $stripe->customers->create([
        'name' => $account->get('name')->value,
        'email' => $account->get('mail')->value,
        'metadata' => ['user_uid' => $user_id]
    ]);
    return $customer;
  }
  
  public function createSubscription($customer_id, $price_id, $free_trial_days = null) {
    try {
      $stripe = new \Stripe\StripeClient($this->key_pub);
      $free_days = ($free_trial_days == null) ? 30 : (int)$free_trial_days;
      $subscription = $stripe->subscriptions->create([
          'customer' => $customer_id,
          'items' => [
            [
              'price' => $price_id,
            ],
          ],
          'trial_end' => time() + $free_days * 24 * 3600,
          //'trial_end' => time() + 30 * 24 * 3600,
      ]);
     } catch(\Stripe\Exception\CardException $e) {
       //todo controle fail
     }
     return $subscription;
  }
  
  public function updateCustomersID($customer_id, $user_id) {
    $account = User::load($user_id);
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $customer = $stripe->customers->update([
        $customer_id,
        'metadata' => ['user_uid' => $user_id]
    ]);
    return $customer;
  }
  
  
  public function getSubscription($customer_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $customer = $stripe->customers->update([
        $customer_id,
        'metadata' => ['user_uid' => $user_id]
    ]);
    return $customer;
  }
  
  
  public function updateSubscriptiondrupal($customer_id, $subscriptions_id, $user_id) {
    $con = \Drupal\Core\Database\Database::getConnection();
    $query = $con->merge('subscription_base_info')
      ->key(['uid' => $user_id])
      ->insertFields([
        'created' => time(),
        'customer_id' => $customer_id,
        'uid' => $user_id,
        'subscription_id' => $subscriptions_id,
      ])
      ->updateFields([
        'updated' => time(),
      ]); // update counter
    $query->execute();
  }
  
  
  public function retrieveCustomers($customer_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->customers->retrieve(
      $customer_id,
      []
    );
    return $result;
  }
  
  public function retrieveSubscription($subscriptions_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->subscriptions->retrieve(
      $subscriptions_id,
      []
    );
    return $result;
  }
  
  public function retrieveInvoice($cus_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->invoices->all(['customer' => $cus_id]);
    return $result;
  }

  public function retrievePrices($subscriptions_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->prices->all(['limit' => 10]);
    return $result;
  }
  
   public function retrievepaymentMethods($cus_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->paymentMethods->all(['customer' => $cus_id, 'type' => 'card',]);
    return $result;
  }
  public function retrievecustomerPortal($cus_id, $url) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    // Authenticate your user.}
    $result =  $stripe->billingPortal->sessions->create([
      'customer' => $cus_id,
      'return_url' => $url,
    ]);
    return $result;
  }
  public function upgradSubscription($sub_id, $price_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $subscription = $stripe->subscriptions->retrieve($sub_id);
    $upgradSubscription = $stripe->subscriptions->update($sub_id, [
        'cancel_at_period_end' => false,
        'proration_behavior' => 'create_prorations',
        'items' => [
          [
            'id' => $subscription->items->data[0]->id,
            'price' => $price_id,
          ],
        ],
      ]);
     return $upgradSubscription;
  }
  public function cancelSubscription($sub_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->subscriptions->update($sub_id,[ 
     'cancel_at_period_end' => true
    ]);
    return $result;
  }
  public function cancelimmediateSubscription($sub_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->subscriptions->cancel($sub_id,[]);
    return $result;
  }

  public function revertcancelSubscription($sub_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->subscriptions->update($sub_id,[
     'cancel_at_period_end' => false
    ]);
    return $result;
  }
  public function addstorageSubscription($sub_id, $price_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->subscriptionItems->create([
      'subscription' => $sub_id,
      'price' => $price_id,
      'quantity' => 1,
    ]);
    return $result;
  }
  public function removeaddstorageSubscription($subitem_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->subscriptionItems->delete(
      $subitem_id,
      []
    );
    return $result;
  }
  public function getpricing($pricing_id) {
    $stripe = new \Stripe\StripeClient($this->key_pub);
    $result = $stripe->prices->retrieve(
      $pricing_id,
      []
    );
    return $result;
  }
}

