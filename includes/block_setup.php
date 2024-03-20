<?php
// =====================================================================
// BLOCK SETUP
// =====================================================================
// All the "Gutenberg" block editor related functions.
// =====================================================================
if ( ! function_exists( 'gutenbergtheme_setup' ) ) {

	function gutenbergtheme_setup() {

    // Add support for full and wide align images.
    add_theme_support( 'align-wide' );

    // Add responsive embeds
    add_theme_support( 'responsive-embeds' );

    // Disable custom font sizes
    add_theme_support('disable-custom-font-sizes');

    // Disable custom colors
    add_theme_support( 'disable-custom-colors' );

    // Add Editor Styles
    add_theme_support('editor-styles');
    add_editor_style( 'style.css' );

    // Color Pallet
    add_theme_support( 'editor-color-palette', array(
      array(
        'name' => 'green',
        'slug' => 'green',
        'color' => '#046A38',
      ),
      array(
        'name' => 'green-500',
        'slug' => 'green-500',
        'color' => '#5C5C5C',
      ),
      array(
        'name' => 'blue',
        'slug' => 'blue',
        'color' => '#008BD6',
      ),
      array(
        'name' => 'black',
        'slug' => 'black',
        'color' => '#000',
      ),
      array(
        'name' => 'gray',
        'slug' => 'gray',
        'color' => '#5C5C5C',
      ),
      array(
        'name' => 'gray-500',
        'slug' => 'gray-500',
        'color' => '#757575',
      ),
      array(
        'name' => 'gray-250',
        'slug' => 'gray-250',
        'color' => '#FAFAFA',
      ),
      array(
        'name' => 'white',
        'slug' => 'white',
        'color' => '#fff',
      ),
      array(
        'name' => 'translucent',
        'slug' => 'translucent',
        'color' => 'rgba(0,0,0,0.6)',
      ),
    ) );
  }

}
add_action( 'after_setup_theme', 'gutenbergtheme_setup' );
?>