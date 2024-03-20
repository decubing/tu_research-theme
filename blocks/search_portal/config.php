<?php

// Sample filtered URL: /portal_filters%5B%5D=department_school-of-medicine

// Block registration
function register_search_portal_block() {

  acf_register_block_type(array(
    'name'              => 'search-portal',
    'title'             => __('Search Portal'),
    'description'       => __('An interface for searching and filtering research projects'),
    'category'          => 'formatting',
    'icon'              => 'admin-site-alt3',
    'keywords'          => array( 'research', 'portal', 'search', 'filter' ),
    'multiple'          => false,
    'render_template'   => 'blocks/search_portal/portal.php',
    'enqueue_assets'    => function(){
      wp_enqueue_style( 'block-search_portal', get_template_directory_uri() . '/blocks/search_portal/portal.css' );
      wp_enqueue_script( 'block-search_portal', get_template_directory_uri() . '/blocks/search_portal/portal.js', array('jquery'), '', true );
    },
  ));

}

// Check if function exists and hook into setup.
if( function_exists('acf_register_block_type') ) {
  add_action('acf/init', 'register_search_portal_block');
}