<?php
// Functions specific to LDAP authorization

/**
 * Update user meta based on LDAP company attribute
 * See https://github.com/uhm-coe/authorizer/issues/52
 */
add_filter( 'authorizer_custom_role', 'trp_authorizer_set_role', 10, 2 );
function trp_authorizer_set_role( $role, $user_data ) {
  $tu_role = $user_data['ldap_attributes'][0]['company'][0] ?? false;

  $user = get_user_by( 'email', $user_data['email'] );
  if ( $user && $tu_role ) {
    $tu_role = strtolower($tu_role);
    // update_user_meta( $user->ID, 'tu_role', $tu_role );
    $user->set_role($tu_role);
    $role = $tu_role;
  }

  return $role;
}