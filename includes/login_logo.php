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
?>