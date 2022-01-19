<?php

namespace Drupal\stripe_subscription_and_customer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Class AbortCancelForm.
 */
class AbortCancelForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abort_cancel_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = NULL) {
    $form['#prefix'] = '<div id="ajax_form_multistep_form"><div class="col-sm-12"><h1>Revert Subscription</h1></div><div id="result-message result_message message"></div>';
    $form['#suffix'] = '</div>';
    $form['markup'] = [
     '#markup' => '<div class="step">Are you sure you want to revert your subscription? So, you can still use Kaboodle Media until then.</div>'
    ];
    $form['uid'] = [
      '#type' => 'hidden',
      '#value' => $uid
    ];
    $form['buttons']['cancel'] = [
      '#type' => 'submit',
      '#value' => t('cancel'),
      '#prefix' => '<div class="d-flex"><div class="step1-button">',
      '#suffix' => '</div>',
      '#limit_validation_errors' => [],
      '#attributes' => ['class' => ['calbutton']],
      '#ajax' => [
        // We pass in the wrapper we created at the start of the form
        'wrapper' => 'ajax_form_multistep_form',
        // We pass a callback function we will use later to render the form for the user
        'callback' => '::ajax_form_multistep_form_ajax_cancel_abort',
        'event' => 'click',
      ],
    ];
    $form['buttons']['forward'] = [
      '#type' => 'submit',
      '#value' => t('Revert Subscription'),
      '#prefix' => '<div class="step1-button">',
      '#suffix' => '</div></div>',
      '#attributes' => ['class' => ['subbutton']],
      '#ajax' => [
        // We pass in the wrapper we created at the start of the form
        'wrapper' => 'ajax_form_multistep_form',
        // We pass a callback function we will use later to render the form for the user
        'callback' => '::ajax_form_change_plan_form_ajax_callback_abort',
        'event' => 'click',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if(isset($values['uid'])) {
      $uid = $values['uid'];
      $account = \Drupal\user\Entity\User::load($uid);
      $customer_id = $account->get('stripe_customer_id')->value;
      if (isset($customer_id) && !empty($customer_id)) {
        $subscription_id = $account->get('stripe_subscription_id')->value;
      }
      \Drupal::service('stripe_subscription_and_customer.stripe_api')->revertcancelSubscription($subscription_id);
    }
  }

  public function ajax_form_change_plan_form_ajax_callback_abort(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $uid = $values['uid'];
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand(new RedirectCommand('/stripe/reload/'.$uid));
    return $response;
  }
  
  public function ajax_form_multistep_form_ajax_cancel_abort(array &$form, FormStateInterface $form_state) {
    $messages = \Drupal::messenger()->deleteAll();
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
