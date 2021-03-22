<?php
// Functions specific to LDAP authorization

/**
 * Determine user role to assign
 * See https://github.com/uhm-coe/authorizer/issues/52
 */
add_filter( 'authorizer_custom_role', 'trp_authorizer_custom_role', 10, 2 );
function trp_authorizer_custom_role( $role, $user_data ) {
  $tu_role = $user_data['ldap_attributes'][0]['company'][0] ?? false;
  $tu_role = strtolower($tu_role);

  // Only approve Student and Faculty roles
  if ( ($tu_role === 'student' || $tu_role === 'faculty') ) {
    return $tu_role;
  }

  return $role;
}

/**
 * Update user meta based on LDAP department attribute
 */
add_filter( 'authorizer_user_register', 'trp_authorizer_set_role_and_meta', 10, 2 );
function trp_authorizer_set_role_and_meta( $user, $user_data ) {
  // If we have a department/schools to use, update user's meta 
  $school = $user_data['ldap_attributes'][0]['department'][0] ?? false;
  if ( $school ) {
    update_user_meta( $user->ID, 'tu_school', $school );
  }
}
