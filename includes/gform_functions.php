<?php

/**
 * Updates auto-generated listing pages.
 * Template page ID is hardcoded in here. Oh well.
 * Changes their post type and sets new content.
 * Rewrites placeholders from the page template.
 * Sets post meta for modifying secondary gravity form:
 *   faculty-defined fields + hidden email field for recipient
 * 
 * Relevant form ID is 1 on local, 3 on staging.
 */
add_action( 'gform_after_create_post', 'trp_update_listings', 10, 3 );
function trp_update_listings( $post_id, $entry, $form ) {
    
  $TEMPLATE_ID = 55;
  $post = get_post( $post_id );
  $template = get_post( $TEMPLATE_ID );
  
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
  update_field('contact_email', $entry[4], $post_id); // TODO fix this; not working but why?
  if (isset($entry[18])) update_field('custom_applicant_question_1', $entry[18], $post_id);
  if (isset($entry[19])) update_field('custom_applicant_question_2', $entry[19], $post_id);
}


/**
 * Updates faculty-defined fields and contact email
 * for application submission forms
 * from their default values.
 * 3 and 4 are the custom question field IDs: 
 *  hide if not submitted
 *  replace otherwise
 * 
 * Relevant form ID is 2 on local, 4 on staging.
 */
add_filter( 'gform_pre_validation', 'trp_set_subform_fields' );
add_filter( 'gform_pre_submission_filter', 'trp_set_subform_fields' );
add_filter( 'gform_admin_pre_render', 'trp_set_subform_fields' );
add_filter( 'gform_pre_render', 'trp_set_subform_fields' );
function trp_set_subform_fields( $form ) {
  global $post;
  if (!$post) return $form;
  if ($form['id'] !== 2 && $form['id'] !== 4) return $form;

  $q1 = get_field('custom_applicant_question_1', $post->ID);
  $q2 = get_field('custom_applicant_question_2', $post->ID);
  $email = get_field('contact_email', $post->ID);

  // IMPORTANT: pass the field by value
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
    }
  }

  return $form;
}