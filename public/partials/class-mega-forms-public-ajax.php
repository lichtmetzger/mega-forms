<?php

/**
 * Mega Forms Ajax Class
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/public/partials
 */

if (!defined('ABSPATH')) {
   exit; // Exit if accessed directly
}

class MF_Public_Ajax extends MF_Ajax
{

   public function submit_form()
   {
      # Get data from tge request
      $has_json = $this->maybe_get_value('has_json');
      $maybe_decode = $has_json !== null ? $has_json : false;
      $form_id = $this->get_value('form_id');
      $context = $this->maybe_get_value('context');
      $posted_data = $this->get_value('posted_data', false, $maybe_decode);
      $args = $this->maybe_get_value('args', false, $maybe_decode);
      $refreshed_fields = array();

      # Process the submission
      mf_submission()->exec($form_id, $posted_data, $context, $args);

      # Prepare the new HTML for the fields that needs refreshing
      if (isset($args['refresh_fields'])) {
         foreach ($args['refresh_fields'] as $field_key) {
            if (($pos = strpos($field_key, '_mfield_')) !== false) {
               $field_id = substr($field_key, $pos + 8);
               $field_wrapper_id = sprintf('mf_%d_field_%d', $form_id, $field_id);
               $field = mf_submission()->form->fields[$field_id] ?? false;
               if ($field) {
                  $fieldObj = MF_Fields::get($field['type'], array('field' => $field));
                  $refreshed_fields['#' . $field_wrapper_id] = $fieldObj->get_the_field(mf_submission()->get_value($field_id));
               }
            }
         }
      }

      # Make any necessary redirections
      if (!mf_submission()->is_empty() && mf_submission()->success) {
         if (mf_submission()->redirect !== false) {
            // If this submission requires a redirect, we don't need to pass any data except from the URL
            $this->success(
               array(
                  'redirect' => mf_submission()->redirect
               )
            );
         } else {
            // prepare response data
            $response = array(
               'keep_form' => mf_submission()->keep_form, // Whether to keep the form in view or hide it
               'scrollTop' => true // Whether to scroll to the success message or not
            );

            // Pass the new HTML for the fields that needs refreshing after the response is recieved/updated  
            if (!empty($refreshed_fields)) {
               $response['refreshedFields'] = $refreshed_fields;
            }

            // Return the response
            $this->success(
               get_mf_submission_msg_html('success', mf_submission()->message),
               apply_filters(
                  'mf_ajax_submit_success_response',
                  $response,
                  $form_id
               )
            );
         }
      } else {
         $response = array();
         // Make sure notices are wrapped in the correct HTML
         if (!empty(mf_submission()->notices)) {
            $response['notices'] = array();
            foreach (mf_submission()->notices as $id => $notice) {
               $field_key = mf_api()->get_field_key($form_id, $id);
               $response['notices'][$field_key] = get_mf_notice_html($notice);
            }
         }
         if (!empty(mf_submission()->compound_notices)) {
            $response['compound_notices'] = array();
            foreach (mf_submission()->compound_notices as $cm_id => $cm_notice) {
               foreach ($cm_notice as $cd_key => $cd_val) {
                  $cm_notice[$cd_key] = get_mf_notice_html($cd_val, 'compound');
               }
               $subfield_key = mf_api()->get_field_key($form_id, $cm_id);
               $response['compound_notices'][$subfield_key] = $cm_notice;
            }
         }

         // Set `scrollTop` to jump to the top after the response is recieved/updated 
         $response['scrollTop'] = true;

         // Pass the new HTML for the fields that needs refreshing after the response is recieved/updated  
         if (!empty($refreshed_fields)) {
            $response['refreshedFields'] = $refreshed_fields;
         }
         // throw an error with the error message + the prepared notices, if available.
         throw new MF_Ajax_Exception(
            get_mf_submission_msg_html('error', mf_submission()->message),
            apply_filters('mf_ajax_submit_fail_response', $response, $form_id)
         );
      }
   }
}
