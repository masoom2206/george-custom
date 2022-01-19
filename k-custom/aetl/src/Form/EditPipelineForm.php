<?php

namespace Drupal\aetl\Form;

use \Drupal\Core\Form;
use \Drupal\Core\Form\FormBase;
use \Drupal\core\Form\FormStateInterface;
use \Drupal\Core\Url;
use \Drupal\Core\Link;
use \Drupal\core\Site\Settings;
use \Drupal\core\StreamWrapper\PublicStream;
use \Symfony\Component\HttpFoundation\Request;
use Aws\Exception\AwsException;

class EditPipelineForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_pipeline_form';
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'aetl.settings',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormstateInterface $form_state, $pid = NULL) {
    $config = \Drupal::config('aetl.settings')->get();
    $messenger = \Drupal::messenger();
    if ($errors = \Drupal::service('aetl')->validate($config)) {
      foreach ($errors as $error) {
        $messenger->addMessage((string)$error, $messenger::TYPE_ERROR);
      }
      $messenger->addMessage('Unable to validate your aetl configuration settings. Please configure aetl File System from the admin/config/media/aetl page and try again.', $messenger::TYPE_ERROR);
      return;
    }
    if (empty($pid)) {
      $messenger->addMessage('Unable to validate Pipeline details. Please configure pipeline ID and try again.', $messenger::TYPE_ERROR);
      return;
    }
    $this->id = $pid;
    $config = \Drupal::config('aetl.settings')->get();
    $transcoder_client = \Drupal::service('aetl')->getAmazonETClient($config);
    $result = $transcoder_client->readPipeline(array(
      // Id is required
      'Id' => $this->id,
    ))->toArray();

    $form['pipeline_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pipeline Name'),
      '#default_value' => $result['Pipeline']['Name'],
      '#required' => TRUE,
    ];
    $form['input_bucket'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Input Bucket'),
      '#default_value' => $result['Pipeline']['InputBucket'],
      '#required' => TRUE
    ];
    $form['output_bucket'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Output Bucket'),
      '#default_value' => $result['Pipeline']['OutputBucket'],
      '#required' => TRUE
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    ];
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('pipeline_name')) < 10) {
      $form_state->setErrorByName('pipeline_name', $this->t('Pipeline name is too short.'));
    }
  }
   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //TODO create AWS Elastic Trancoder services
    $form_state->setRedirect('aetl.pipelineslist');
    $data['Id'] = $this->id;
    $data['Name'] = $form_state->getValue('pipeline_name');
    $data['InputBucket'] = $form_state->getValue('input_bucket');
    $data['OutputBucket'] = $form_state->getValue('output_bucket');
    $transcoder_client = \Drupal::service('aetl')->updatePipeline($data);
    drupal_set_message("Pipeline Updated succesfully.");
    $form_state->setRedirect('aetl.pipelineslist');
  }
}