<?php
// Register Custom Post Types
add_action( "init", "trp_register_custom_post_types", 0 );
function trp_register_custom_post_types() {

	$args = trp_cpt_default_args("Research Listing", "Research Listings", "Listings for research areas");
	// $args['template'] = [
	// 	['core/cover'],
	// 	['core/heading'],
	// 	['core/group'],
	// 	['core/paragraph'],
	// 	['core/heading', ["level" => 3]],
	// 	['core/paragraph'],
	// 	['core/heading', ["level" => 3]],
	// 	['core/paragraph'],
	// 	['core/table'],
	// 	['core/heading', ["level" => 3]],
	// 	['core/paragraph'],
	// 	['gravityforms/form'],
	// ];
	// $args['template_lock'] = 'all';

	register_post_type( "research-listing", $args );

}



// // Disable block editor for ACF CPTs - just put the data in and don't worry about the formatting
// add_filter( "use_block_editor_for_post", "trp_disable_block_editor_for_events", 10, 2 );
// function trp_disable_block_editor_for_events( $use_block_editor, $post ) {
//   if ( $post->post_type === "research-page" ) {
//       return false;
//   }
//   return $use_block_editor;
// }

// ---------------------------------------
// --------------- Helpers ---------------
// ---------------------------------------

// Generate default arguments for registering a custom post type
function trp_cpt_default_args( $singular, $plural, $description, $icon_slug='dashicons-calendar-alt' ) {
	$labels = [
		"name"                  => _x( "$plural", "Post Type General Name", "trp_td" ),
		"singular_name"         => _x( "$singular", "Post Type Singular Name", "trp_td" ),
		"menu_name"             => __( "$plural", "trp_td" ),
		"name_admin_bar"        => __( "$plural", "trp_td" ),
		"archives"              => __( "", "trp_td" ),
		"attributes"            => __( "", "trp_td" ),
		"parent_item_colon"     => __( "", "trp_td" ),
		"all_items"             => __( "All $plural", "trp_td" ),
		"add_new_item"          => __( "Add New $singular", "trp_td" ),
		"add_new"               => __( "Add New", "trp_td" ),
		"new_item"              => __( "New $singular", "trp_td" ),
		"edit_item"             => __( "Edit $singular", "trp_td" ),
		"update_item"           => __( "Update $singular", "trp_td" ),
		"view_item"             => __( "View $singular", "trp_td" ),
		"view_items"            => __( "View $plural", "trp_td" ),
		"search_items"          => __( "Search $plural", "trp_td" ),
		"items_list"            => __( "$plural list", "trp_td" ),
		"items_list_navigation" => __( "$plural list navigation", "trp_td" ),
		"filter_items_list"     => __( "Filter $plural list", "trp_td" ),
	];
	return [
		"label"                 => __( "$singular", "trp_td" ),
		"description"           => __( $description, "trp_td" ),
		"labels"                => $labels,
		"supports"              => [ "title", "custom-fields", 'editor', 'thumbnail', 'excerpt', 'author' ],
		"taxonomies"            => [ "category", "post_tag" ],
		"hierarchical"          => false,
		"public"                => true,
		"show_ui"               => true,
		"show_in_menu"          => true,
		"menu_position"         => 5,
		"menu_icon"             => $icon_slug,
		"show_in_admin_bar"     => true,
		"show_in_nav_menus"     => false,
		"can_export"            => true,
		"has_archive"           => false,
		"exclude_from_search"   => true,
		"publicly_queryable"    => true,
		"capability_type"       => "page",
		"show_in_rest"          => true,
	];
}