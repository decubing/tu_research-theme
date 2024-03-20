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

    // ldaps://auth.tulane.edu
    // port: 636
    // search base: 
    //  ou=Accounts,dc=tulane,dc=local
    //  dc=tulane,dc=local
    //  attr with username : sAMAccountName
    //  attr with email : mail
    // ldap directory user: CN=itwpresearchnetbind,OU=Technology Services,OU=FIM Managed,OU=Service Accounts,DC=tulane,DC=local
    // ldap directory pw: 5z7Gj#!Qx
    //  pw url: https://password.tulane.edu
    // attr with first name: givenName
    // attr with last name: sn
    global $wpdb;

    $config = array(
        'ldap'                      => '1',
        'ldap_host'                 => 'ldaps://auth.tulane.edu:636',
        'ldap_port'                 => '636',
        'ldap_tls'                  => '0',
        'ldap_search_base'          => 'ou=Accounts,dc=tulane,dc=local \n dc=tulane,dc=local',
        'ldap_search_filter'        => '',
        'ldap_uid'                  => 'sAMAccountName',
        'ldap_attr_email'           => 'mail',
        'ldap_user'                 => 'CN=itwpresearchnetbind,OU=Technology Services,OU=FIM Managed,OU=Service Accounts,DC=tulane,DC=local',
        'ldap_password'             => '5z7Gj#!Qx',
        'ldap_lostpassword_url'     => '',
        'ldap_attr_first_name'      => 'givenName',
        'ldap_attr_last_name'       => 'sn',
        'ldap_attr_update_on_login' => '',
        'ldap_test_user'            => '',
    );
   /*  $user = $_POST["username"];
    $pw = $_POST['password'];
    $out = [];
    $debug = [];
    $out["result"] = authenticate_ldap($config, $user, $pw, $debug );
    
    $out["debug"] = $debug;
    print_r($out); 
    die; */
}

require_once("acf-fields.php");