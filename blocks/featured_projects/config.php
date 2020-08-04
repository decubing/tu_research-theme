<?php
// Block registration
function register_featured_projects_block() {

  acf_register_block_type(array(
    'name'              => 'featured-projects',
    'title'             => __('Featured Projects'),
    'description'       => __('A grid display of up to three featured project cards'),
    'category'          => 'formatting',
    'icon'              => 'images-alt',
    'keywords'          => array( 'research', 'projects', 'grid', 'cards' ),
    'multiple'          => false,
    'render_template'   => 'blocks/featured_projects/featured_projects.php',
  ));

}

// Check if function exists and hook into setup.
if( function_exists('acf_register_block_type') ) {
  add_action('acf/init', 'register_featured_projects_block');
}