<?php
// Functions specific to LDAP authorization

/**
 * Update user role and meta based on LDAP company attribute
 * See https://github.com/uhm-coe/authorizer/issues/52
 */
add_filter( 'authorizer_custom_role', 'trp_authorizer_set_role_and_meta', 10, 2 );
function trp_authorizer_set_role_and_meta( $role, $user_data ) {
  $tu_role = $user_data['ldap_attributes'][0]['company'][0] ?? false;
  $tu_role = strtolower($tu_role);

  // Only update Student and Faculty roles
  if ( !($tu_role === 'student' || $tu_role === 'faculty') ) {
    return $role;
  }

  // Update user's role
  $user = get_user_by( 'email', $user_data['email'] );
  if ( $user ) {
    $user->set_role($tu_role);
    $role = $tu_role;

    //Update user's meta
    $school = $user_data['ldap_attributes'][0]['department'][0] ?? false;
    if ( $school ) {
      update_user_meta( $user->ID, 'tu_school', $school );
    }
  }

  return $role;
}