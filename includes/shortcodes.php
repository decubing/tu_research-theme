<?php

function private_content($atts, $content = null) {
    $a = shortcode_atts( array(
		'role' => 'visitor-only',
	), $atts );

    $user = wp_get_current_user();
    if ( 
        $a["role"] == "visitor-only" && 
        !in_array( "subscriber", (array) $user->roles) &&
        !in_array( "administrator", (array) $user->roles)
        ) {
        return '<div class="visible-only-content">' . $content . '</div>';
    };
    if ( 
        $a["role"] == "subscriber" && 
        (
            in_array( "subscriber", (array) $user->roles) ||
            in_array( "administrator", (array) $user->roles)
        )
        ) {
        return '<div class="subsriber-content">' . do_shortcode($content) . '</div>';
    };

}
add_shortcode('private', 'private_content');




?>