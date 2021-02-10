<?php
// =====================================================================
// WIDGET SETTINGS
// =====================================================================
// All sidebar and widget related functions.
// =====================================================================

// Intialize Sidebars
function theme_login_logo() { ?>
  <style type="text/css">
      #login h1 a, .login h1 a {
        background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/login-logo.png);
        height: 70px;
        width: 70px;
        background-size: 70px 70px;
        background-repeat: no-repeat;
        padding-bottom: 30px;
      }
  </style>
<?php }
add_action( 'login_enqueue_scripts', 'theme_login_logo' );

function trp_login_logo_url() {
  return home_url();
}
add_filter( 'login_headerurl', 'trp_login_logo_url' );

function trp_login_logo_url_title() {
  return 'Tulane Research Network';
}
add_filter( 'login_headertitle', 'trp_login_logo_url_title' );
?>