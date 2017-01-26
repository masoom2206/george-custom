<?php
// This file must be at percent_complete_meter/includes/views directory.

/**
 * @file
 * Definition of lms_handler_field_web_listing_path.
 */

/**
 * Description of what my handler does.
 */
class lms_handler_field_web_listing_path extends views_handler_field {
   function construct() {

    }
     
    /**
    * Loads additional fields.
    */
    function query() {
    }
     
    /**
    * Default options form.
    */
    function option_definition() {
    $options = parent::option_definition();
    return $options;
    }
     
    /**
    * Creates the form item for the options added.
    */
    function options_form(&$form, &$form_state) {
      parent::options_form($form, $form_state);
    }
  

  /**
   * Renders the field handler.
   */
  function render($values) {
	$output = '';
	//$nid = $values->_field_data['nid']['entity']->field_lms_listing_reference['und']['0']['nid'];
	$nid = $values->nid;
	$web_active = db_select("cbone_website_settings", 'ws');
	if($active == 1){
		$urlAlias = drupal_lookup_path('alias', "node/".$nid);
	}
	return $output;
  }
}