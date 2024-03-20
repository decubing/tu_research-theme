<?php

add_action("init", "blockly_register_custom_taxonomies", 0);
function blockly_register_custom_taxonomies() {
  blockly_register_custom_taxonomy( 'Topic', 'Topics', 'research-listing' );
  blockly_register_custom_taxonomy( 'School', 'Schools', 'research-listing' );
  blockly_register_custom_taxonomy( 'Department', 'Departments', 'research-listing' );
}

// ---------------------------------------
// --------------- Helpers ---------------
// ---------------------------------------
function blockly_register_custom_taxonomy ($singular, $plural, $object_type='post') {
  $slugified_name = strtolower($singular);
  $slugified_name = preg_split("/(-|_| )+/", $slugified_name);
  $slugified_name = join('-', $slugified_name);
  $args = array(
    "hierarchical" => false, // true = category-like, false = tag-like
    "labels" => array(
      "name" => _x( "$plural", "taxonomy general name" ),
      "singular_name" => _x( "$singular", "taxonomy singular name" ),
      "search_items" =>  __( "Search $plural" ),
      "all_items" => __( "All $plural" ),
      "parent_item" => __( "Parent $singular" ),
      "parent_item_colon" => __( "Parent $plural:" ),
      "edit_item" => __( "Edit $singular" ),
      "update_item" => __( "Update $singular" ),
      "add_new_item" => __( "Add New $singular" ),
      "new_item_name" => __( "New $singular Name" ),
      "menu_name" => __( "$plural" ),
    ),
    'show_in_rest' => true, // Need this for it too show up in the block editor
    "query_var" => true,
    "show_admin_column" => true,
    
  );
  register_taxonomy($slugified_name, $object_type, $args);
}