<?php

namespace Drupal\generate_pdf\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File;
use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;
use TCPDF;
use TCPDF_FONTS;
use Aws\S3\S3Client;
/**
 * Class AddFontForm.
 */
class AddFontForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_font_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['drop_file_ttf_or_otf'] = [
      '#type' => 'plupload',
      '#title' => $this->t('Drop file: ttf or otf'),
      '#description' => $this->t('With this converter you can convert your fonts for the php TCPDF library.'),
      '#upload_validators' => array(
        'file_validate_extensions' => array('ttf otf'),
       ),
      '#weight' => '0',
    ];
    $form['default_location_for_font_save'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default location for font save'),
      '#maxlength' => 128,
      '#size' => 128,
      '#weight' => '0',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
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
    // Display result.
  //  foreach ($form_state->getValues() as $key => $value) {
  //    \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
  //  }
    $data = $form_state->getValues();
    $fonts = $data['drop_file_ttf_or_otf'];
    //print_r($fonts);$font['name']
    $i = 0;
    foreach($fonts as $font){
      $ttfFile = \Drupal::service('file_system')->realpath($font['tmppath']);
      //$destination = "public://kmfonts/". $font['name'];
      $uploaded_file = $font;
      $scheme = '/var/tmp/';
      if ($uploaded_file['status'] == 'done') {
         file_prepare_directory($scheme, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
         $fileLocation = $scheme . '/' .  $font['name'];
         $file =  \Drupal::service('file_system')->saveData(file_get_contents($ttfFile), $fileLocation, FileSystemInterface::EXISTS_REPLACE);
          //print_r($file); exit;
         //$ttfFilenew = \Drupal::service('file_system')->realpath($fileLocation);
        /* if(file_exists($ttfFile)) {
            $ttfFilenew = \Drupal::service('file_system')->realpath($fileLocation);
            $fontname = TCPDF_FONTS::addTTFfont($ttfFile , 'TrueTypeUnicode', '', 96);
            \Drupal::messenger()->addMessage( "The processed font '$ttfFile' can be used with the name: $fontname");
         }*/
         // Check the $file object to see if all necessary properties are set. Otherwise, use file_load($file->fid) to populate all of them.
         // $file = file_load($file->fid);
         $pathTTFFiles[$i]['file'] = $file;
         $pathTTFFiles[$i]['name'] = $font['name'];
         $i++;
     }
    }
    /*$pathTTFFiles = [
      '/var/tmp/o_1eist85qn1mc1ngt1uiej4757e.tmp'
     ];*/
    foreach($pathTTFFiles as  $ttfFile){
       $fontname = TCPDF_FONTS::addTTFfont($ttfFile['file'], 'TrueTypeUnicode', '', 96);
//       \Drupal::messenger()->addMessage("The processed font ".$ttfFile['file']." can be used with the name: $fontname");
       if(!empty($fontname)) {
         $config = \Drupal::config('s3fs.settings')->get();
         $S3 = \Drupal::service('s3fs')->getAmazonS3Client($config);
         $key = 's3fs_public/kmfonts/'.$ttfFile['name'];
         $result = $S3->putObject(['SourceFile' => $ttfFile['file'], 'Bucket' => $config['bucket'], 'Key' => $key, 'ACL' => 'public-read']);
         $url = $result['ObjectURL'];
         $database = \Drupal::database();
         $database->merge('import_fonts')
           ->key('tcpdf_name', $fontname)
           ->fields([
            'uid' => \Drupal::currentUser()->id(),
            'name' => $ttfFile['name'],
	    'font_path_s3' => $key,
            'full_s3' => $url,
         ])
         ->execute();
          \Drupal::messenger()->addMessage("The processed font ".$ttfFile['file']." can be used with the name: $fontname");
       } else {
          \Drupal::messenger()->addMessage("The processed font ".$ttfFile['file']." can't uploaded", TYPE_ERROR);
       }
    }
  }
}
