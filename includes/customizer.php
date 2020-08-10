<?php
// =====================================================================
// CUSTOMIZER SETTINGS
// =====================================================================
// All the theme-related Customizer settings are
// registered here.
// =====================================================================
function theme_customizer_register( $wp_customize ) {
  
  // Add Controls & Settings to Existing Sections
  $wp_customize->add_setting( 'theme_logo' );
  $wp_customize->add_setting( 'theme_copyright_info' );
  $wp_customize->add_control( 
    new WP_Customize_Media_Control( $wp_customize, 'theme_logo',
      array(
        'label' => 'Logo',
        'section' => 'title_tagline',
        'settings' => 'theme_logo',
      ) 
    ) 
  );
  $wp_customize->add_control( 'theme_copyright_info',
    array(
      'label' => 'Copyright Info',
      'section' => 'title_tagline',
      'type' => 'text',
    )
  );

  $wp_customize->add_setting( 'theme_template_post_id' );
  $wp_customize->add_control( 'theme_template_post_id',
    array(
      'label' => 'Listing Template ID',
      'description' => 'The ID of a page to use as the template for research listings',
      'section' => 'title_tagline',
      'type' => 'number',
    )
  );

  $wp_customize->add_setting( 'theme_applicant_form_id' );
  $wp_customize->add_control( 'theme_applicant_form_id',
    array(
      'label' => 'Applicant Form ID',
      'description' => 'The ID of a form to use for applicants on research listing pages',
      'section' => 'title_tagline',
      'type' => 'number',
    )
  );

  // 3 featured projects
  foreach ([1,2,3] as $num) {
    $wp_customize->add_setting( "trp_featured_projects_$num", array(
      'capability' => 'edit_theme_options',
      'sanitize_callback' => 'trp_sanitize_featured_projects',
    ) );
    $wp_customize->add_control("trp_featured_projects_$num", [
      'label'    => "Featured Project $num",
      'description' => 'Select up to three featured projects to showcase across the site',
      'section'  => 'title_tagline',
      'type'     => 'select',
      'choices' => trp_research_listings()
    ]
  );
  }

  
  // Ensure input is an absolute integer that correspond to published pages.
  function trp_sanitize_featured_projects( $input, $setting ) {
    if ( get_post_status( (int)$input ) === 'publish' ) {
        return $input;
    } else {
      return false;
    }
  }
}
add_action( 'customize_register', 'theme_customizer_register' );

function trp_research_listings() {
  $projects = get_posts([
    'post_type' => 'research-listing',
    'posts_per_page' => -1,
    'fields' => [
      'id',
      'title'
    ]
  ]);

  $pjs = [];
  foreach ($projects as $project) {
    $pjs[$project->ID] = $project->post_title;
  }

  return $pjs;
}

?>