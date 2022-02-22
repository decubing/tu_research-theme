<?php

/**
 * Updates auto-generated listing pages.
 * Template page ID set through customizer: theme_template_post_id
 * Changes their post type and sets new content.
 * Rewrites placeholders from the page template.
 * Sets post meta for modifying secondary gravity form:
 *   faculty-defined fields + hidden email field for recipient
 */
add_action( 'gform_after_create_post', 'trp_update_listings', 10, 3 );
function trp_update_listings( $post_id, $entry, $form ) {
    
  $TEMPLATE_ID = (int)get_theme_mod('theme_template_post_id');
  $post = get_post( $post_id );
  $template = get_post( $TEMPLATE_ID ); // TODO:
  if (!$post || !$template) return; // something went wrong
  
  // $test = [];
  // foreach ($form['fields'] as $ff) { $test[$ff->id] = $ff->label; }

  // Update post properties
  $post->post_type = 'research-listing';
  $post->post_content = $template->post_content;
  $post->post_title = $entry[22]; // Project title
  $post->post_excerpt = $entry[9]; // Project teaser
  
  // Content placeholder rewriting map
  $rewrite_map = [
    'TEASER_REWRITE' => $entry[9],
    'DESCRIPTION_REWRITE' => $entry[21],
    'OUTCOME_REWRITE' => $entry[10],
    'WORKLOAD_REWRITE' => $entry[11],
    'SKILLS_REWRITE' => $entry[13],
    'PAID_REWRITE' => $entry[12],  
    'ELIGIBLE_REWRITE' => $entry[14],
    'PARTNERS_REWRITE' => $entry[15],
    'SPONSORS_REWRITE' => $entry[16],
  ];

  // Special case for the post thumbnail
  preg_match('/url\((.+)\)/', $template->post_content, $image_rewrite);
  $tmp_img_url = $image_rewrite[1] ?? false;
  $post_img_url = get_the_post_thumbnail_url($post->ID);
  if ($tmp_img_url && $post_img_url) 
    $rewrite_map[$tmp_img_url] = $post_img_url;
  // Set thumbnail to default if no image uploaded via form
  if ($tmp_img_url && !$post_img_url && get_post_thumbnail_id($TEMPLATE_ID)) 
    set_post_thumbnail($post->ID, get_post_thumbnail_id($TEMPLATE_ID));

  // Rewrite post content placeholders
  foreach ($rewrite_map as $placeholder => $new_value) {
    $post->post_content = str_replace($placeholder, $new_value, $post->post_content);
  }

  // $tacos = 1;
  wp_update_post( $post );

  // Update ACF fields after the post is updated
  update_field('contact_email', $entry[4], $post_id);
  if (isset($entry[5])) update_field('school', $entry[5], $post_id); // update school name
  if (isset($entry[25])) update_field('faculty_name', $entry[25], $post_id); // update faculty name
  if (isset($entry[18])) update_field('custom_applicant_question_1', $entry[18], $post_id);
  if (isset($entry[19])) update_field('custom_applicant_question_2', $entry[19], $post_id);
  if (isset($entry[23])) update_field('additional_applicant_uploads', $entry[23], $post_id);
}


/**
 * Updates faculty-defined fields and contact email
 * for application submission forms
 * from their default values.
 * 3, 4, and 6 are the custom question field IDs: 
 *  hide if not submitted
 *  replace otherwise
 * 
 * 5 is the mailto email field
 * 
 * Relevant form ID is 2 on local, 4 on staging.
 */
add_filter( 'gform_pre_validation', 'trp_set_subform_fields' );
add_filter( 'gform_pre_submission_filter', 'trp_set_subform_fields' );
add_filter( 'gform_admin_pre_render', 'trp_set_subform_fields' );
add_filter( 'gform_pre_render', 'trp_set_subform_fields' );
function trp_set_subform_fields( $form ) {
  global $post;
  $FORM_ID = (int)get_theme_mod('theme_applicant_form_id');
  if (!$post) return $form;
  if ($form['id'] !== $FORM_ID) return $form;

  $q1 = get_field('custom_applicant_question_1', $post->ID);
  $q2 = get_field('custom_applicant_question_2', $post->ID);
  $uploads = get_field('additional_applicant_uploads', $post->ID);
  $email = get_field('contact_email', $post->ID);

  // IMPORTANT: pass the field by reference
  foreach ($form['fields'] as &$ff) {
    switch ($ff->id) {
      case 3: 
        if ($q1) $ff->label = $q1; 
        else $ff->visibility = 'hidden';
        break;
      case 4: 
        if ($q2) $ff->label = $q2; 
        else $ff->visibility = 'hidden';
        break;
      case 5:
        $ff->defaultValue = $email ?: '';
        break;
      case 6:
        if ($uploads) $ff->label = $uploads; 
        else $ff->visibility = 'hidden';
        break;
    }
  }

  return $form;
}

/**
 * Prepopulates Email and Name fields of forms with logged in user's data.
 * Also prepopulates school, if available.
 * Does nothing if no logged in user.
 */
add_filter( 'gform_pre_render', 'trp_prepopulate_fields' );
function trp_prepopulate_fields( $form ) {
  $userdata = wp_get_current_user();
  if ($userdata->ID === 0) {
    return $form;
  }

  $firstname = $userdata->user_firstname ?? '';
  $lastname = $userdata->user_lastname ?? '';
  if (!$firstname && !$lastname) {
    $fullname = $userdata->display_name;
  } else {
    $fullname =  trim( "$firstname $lastname" );
  }
  $email = $userdata->user_email;
  $school = get_user_meta($userdata->ID, 'tu_school', true);

  // IMPORTANT: pass the field by reference
  foreach ($form['fields'] as &$ff) {
    switch ($ff->label) {
      case 'Name':
        $ff->defaultValue = $fullname;
        break;
      case 'Email':
        $ff->defaultValue = $email;
        break;
      case 'School':
        $ff->defaultValue = $school;
        break;
      case 'Name of Organization or Newcomb-Tulane Department':
        $ff->defaultValue = $school;
        break;
    }
  }
  return $form;
}