<?php
// Functions specific to LDAP authorization

/**
 * Update user meta based on LDAP affiliation attribute
 * See https://github.com/uhm-coe/authorizer/issues/52
 */
add_filter( 'authorizer_custom_role', 'trp_authorizer_add_affiliation_usermeta', 10, 2 );
function trp_authorizer_add_affiliation_usermeta( $role, $user_data ) {
  $affiliation = $user_data['ldap_attributes'][0]['affiliation'][0] ?? 'Unknown';

  $user = get_user_by( 'email', $user_data['email'] );
  if ( false !== $user ) {
    update_user_meta( $user->ID, 'tu_affiliation', $affiliation );
  }

  return $role;
}

/**
 * Redirect students and non-users from listing application form page.
 */
add_action( 'template_redirect', 'trp_restrict_listing_form' );
function trp_restrict_listing_form() {
  // Admins exempted from this check
  if (current_user_can('administrator')) return;
  // Check if current page is the listing form page
  if (get_queried_object_id() === (int)get_theme_mod('theme_listing_form_page_id')) {
    $tu_affil = get_user_meta( get_current_user_id(), 'tu_affiliation', true );
    // Students and non-affiliates restricted
    if ($tu_affil === 'Student' || $tu_affil === 'Unknown' || $tu_affil === false) {
      nocache_headers();
      wp_safe_redirect( home_url() );
      exit;
    }
  }
}

/**
 * Remove "List a project" for students and non-users from nav.
 * https://wordpress.stackexchange.com/questions/233667/how-to-hide-an-item-from-a-menu-to-logged-out-users-without-a-plugin
 */
add_filter( 'wp_setup_nav_menu_item', 'trp_filter_nav_item' );
function trp_filter_nav_item( $item ) {
  // Admins exempted from this check
  if (current_user_can('administrator')) return;

  $tu_affil = get_user_meta( get_current_user_id(), 'tu_affiliation', true );
  // Students, non-affiliates, and non-users restricted
  if ($tu_affil === 'Student' || $tu_affil === 'Unknown' || $tu_affil === false) {
    $item->_invalid = ('List a Project' === $item->title)
    ;
  }

  return $item;
}