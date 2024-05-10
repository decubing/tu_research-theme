<?php

function register_ldap_content_block() {

    acf_register_block_type(array(
    'name'              => 'tulane-research-network-ldap-content-block',
    'title'             => __('LDAP Content Block'),
    'description'       => __('Block for showing or hiding content when users are logged into LDAP.'),
    'category'          => 'widgets',
    'icon'              => 'unlock',
    'keywords'          => array( 'login', 'ldap', 'sign in' ),
    'multiple'          => false,
    'render_template'   => get_template_directory_uri() .'/blocks/ldap_content_block/ldap-content-block-render-template.php',
    'enqueue_style' => get_template_directory_uri() . '/blocks/ldap_content_block/style.css',
    'enqueue_script' => get_template_directory_uri() . '/blocks/ldap_content_block/ldap-content-block.js',
    ));

}

// Check if function exists and hook into setup.
if( function_exists('acf_register_block_type') ) {
add_action('acf/init', 'register_ldap_content_block');
}

// Add the ajax call to LDAP
add_action("wp_ajax_LDAP_login", "ldap_content_block_login_callback");
add_action("wp_ajax_nopriv_LDAP_login", "ldap_content_block_login_callback");

require_once('inc/ldap-authorize.php');

/* echo "<pre>";
echo 'Current PHP version: ' . phpversion();
$inipath = php_ini_loaded_file();
if ($inipath) {
    echo 'Loaded php.ini: ' . $inipath;
} else {
    echo 'A php.ini file is not loaded';
}
print_r(get_loaded_extensions());
echo "</pre>";
 */
function ldap_content_block_login_callback(){

    
}

require_once("acf-fields.php");