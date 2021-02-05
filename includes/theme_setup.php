<?php
// =====================================================================
// THEME SETUP
// =====================================================================
// Global theme settings.
// =====================================================================
if ( ! function_exists( 'theme_setup' ) ) :

  // Setup Theme Defaults
  function theme_setup() {
  	 
  	// Add default posts and comments RSS feed links to head.
  	add_theme_support( 'automatic-feed-links' );
  	  
  	// Let WordPress manage the document title.
  	add_theme_support( 'title-tag' );
      
    // Support custom post thumbnails
    add_theme_support('post-thumbnails');
  
  }

endif;
add_action( 'after_setup_theme', 'theme_setup' );  

// Set the content width in pixels, based on the theme's design and stylesheet.
function theme_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'content_width', 580 );
}
add_action( 'after_setup_theme', 'theme_content_width', 0 );

// Add responsive container to embeds
function alx_embed_html( $html ) {
  return '<div class="video-container">' . $html . '</div>';
}
add_filter( 'embed_oembed_html', 'alx_embed_html', 10, 3 );

// Enqueue Styles & Scripts
function theme_scripts_and_styles() {
  
  // Styles
	wp_enqueue_style( 'theme-style', get_stylesheet_uri() );	
	
	// Scripts
  wp_enqueue_script( 'theme_header_navigation', get_template_directory_uri().'/scripts/themeHeaderNavigation.js', array( 'jquery' ) );
  if ( is_singular() ) 
    wp_enqueue_script( 'comment-reply' );

}
add_action( 'wp_enqueue_scripts', 'theme_scripts_and_styles' );

/**
 * Redirect user after successful login to the page they were previously on.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function trp_login_redirect( $redirect_to, $request, $user ) {
  // admins go to wp-admin, everyone else goes to home
  $redirect_to = home_url();
  if ( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
    if ( $user->has_cap( 'administrator' ) ) {
      $redirect_to = admin_url();
    }
  }
  // only apply referer redirect if the cookie is nonempty
  if ( isset($_COOKIE['login_redirect_url']) && $_COOKIE['login_redirect_url'] ) {
    $redirect_to = $_COOKIE['login_redirect_url'];
  }
  return $redirect_to;
}
add_filter( 'login_redirect', 'trp_login_redirect', 1000, 3 );

// Add redirect url cookie on login page
function trp_store_login_referer() {
  if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
    // add redirect if...
    // 1) We have a referer
    // 2) That referer is internal
    // 3) that referer is not the same page we're currently on (to avoid infinite redirects)
    if (
      isset($_SERVER['HTTP_REFERER']) 
      && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false
      && strpos($_SERVER['HTTP_REFERER'], $_SERVER['REQUEST_URI']) === false
    ) {
      setcookie('login_redirect_url', $_SERVER['HTTP_REFERER']);
    } elseif (!isset($_SERVER['HTTP_REFERER'])) {
      // if we've landed here without a referer, clear the redirect cookie
      if (isset($_COOKIE['login_redirect_url'])) {
        setcookie('login_redirect_url', '', time() - 3600);
      }
    }
  }
}
add_action('init', 'trp_store_login_referer', 5, 0);

// Clear redirect cookie on login
function trp_clear_login_redirect_url() {
  if (isset($_COOKIE['login_redirect_url'])) {
    setcookie('login_redirect_url', '', time() - 3600);
  }
}
add_action('wp_login', 'trp_clear_login_redirect_url');
?>
