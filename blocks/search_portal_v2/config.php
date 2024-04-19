<?php
/**
 * Plugin Name:       Search Portal v2
 * Description:       Block for displaying a filtered list of projects.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       skilltype-use-cases
 *
 * @package           create-block
 */

 /**
  * Callback for frontend react to load into
  */

function search_portal_v2_block_render_callback() {
	return '<div id="search_portal_v2_block" class="alignwide"></div>';
}



/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function search_portal_v2_block_init() {
	// use cases block
	wp_register_script(
		'search_portal_v2_frontend',
		get_template_directory_uri() . '/blocks/search_portal_v2/build/search_portal/view.js',
		array('wp-element', 'wp-data', 'wp-core-data', 'wp-api'),
		null,
		true
	);

	register_block_type( __DIR__ . '/build/search_portal', array(
        'render_callback' => 'search_portal_v2_block_render_callback',
		'script' => 'search_portal_v2_frontend'
    ) );


}
add_action( 'init', 'search_portal_v2_block_init' );

