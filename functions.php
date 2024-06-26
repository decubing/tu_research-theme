<?php
//======================================================================
// THEME FUNCTIONS
//======================================================================
// All of the theme functions are kept in the "includes/"
// folder. IMPORTANT: Theme functions should only impact
// style of this particular theme. Any universal functions
// should probably be loaded as a plug-in.
// =====================================================================

// Default Width, Title Tag, and Other Setup Functions
require_once('includes/theme_setup.php' );

// Update Customizer
require_once('includes/customizer.php' );

// Block Setup
require_once('includes/block_setup.php' );

// Menus
require_once('includes/menus.php' );

// Admin Notices
require_once('includes/admin_notices.php' );

// ACF Settings
require_once('includes/acf_settings.php' );

// Widget Settings
require_once('includes/widget_settings.php' );

// Custom Post Types
require_once('includes/custom_post_types.php' );

// Custom Taxonomies
require_once('includes/custom_taxonomies.php' );

// Gravity Forms Functions
require_once('includes/gform_functions.php' );

// Custom search portal block
require_once('blocks/search_portal/config.php' );

// Featured Projects block
require_once('blocks/featured_projects/config.php' );

// Featured Projects block
require_once('blocks/ldap_content_block/config.php' );

// Featured Projects block
require_once('blocks/search_portal_v2/config.php' );

// Update Logo
require_once('includes/login_logo.php' );

// Endpoints
require_once('includes/custom_api_endpoints.php' );

// LDAP Authorization
//require_once('includes/ldap_auth.php' );

// Shortcodes
//require_once('includes/shortcodes.php');

?>