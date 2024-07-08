<?php

/**
 * Get category listing, limited to SoM projects
 *
 * @param array $data Options for the function.
 * @return string|null Post title for the latest, * or null if none.
 */
function get_som_cats()
{
    $the_query = new WP_Query( array(
        'post_type' => 'research-listing',
        'tax_query' => array(
            array (
                'taxonomy' => 'school',
                'field' => 'slug',
                'terms' => 'school-of-medicine',
            )
        ),
    ) );    
    $som_projects = $the_query -> posts;
    // NEW: make array of the post IDs in one step
    $som_projects_ids = wp_list_pluck( $som_projects, 'ID' );
    // get the terms
    $my_terms = wp_get_object_terms( $som_projects_ids, 'category' );

    return $my_terms;
}

add_action('rest_api_init', function () {
    register_rest_route('tu-research-theme/v1', '/som-cats/', array(
        'methods' => 'GET',
        'callback' => 'get_som_cats',
        'permission_callback' => '__return_true',
    ));
});

function get_som_topics()
{
    
    $the_topic_query = new WP_Query( array(
        'post_type' => 'research-listing',
        'tax_query' => array(
            array (
                'taxonomy' => 'school',
                'field' => 'slug',
                'terms' => 'school-of-medicine',
            )
        ),
    ) );    
    $som_projects = $the_topic_query -> posts;
    // NEW: make array of the post IDs in one step
    $som_projects_ids = wp_list_pluck( $som_projects, 'ID' );
    $ids = $som_projects_ids;
    // get the terms
    /* $my_terms = wp_get_object_terms( $som_projects_ids, 'topic' );
    return $my_terms; */
    $placeholders = array_fill(0, count($ids), '%d');
    $format = implode(', ', $placeholders);
    global $wpdb;
    $results = $wpdb->get_results( $wpdb->prepare(
        " SELECT terms.name, terms.term_id, COUNT(tr.object_id) as count FROM {$wpdb->terms} terms " .
        " INNER JOIN {$wpdb->term_taxonomy} tt ON (tt.term_id = terms.term_id) " .
        " INNER JOIN {$wpdb->term_relationships} tr ON (tr.term_taxonomy_id = tt.term_taxonomy_id) " .
        " WHERE tr.object_id IN ({$format})". 
        " AND tt.taxonomy = 'topic' ".
        "GROUP BY terms.term_id "
        ,
        $ids
    ) );
    return $results; 
}

add_action('rest_api_init', function () {
    register_rest_route('tu-research-theme/v1', '/som-topics/', array(
        'methods' => 'GET',
        'callback' => 'get_som_topics',
        'permission_callback' => '__return_true',
    ));
});

function get_som_departments()
{
    $the_query = new WP_Query( array(
        'post_type' => 'research-listing',
        'tax_query' => array(
            array (
                'taxonomy' => 'school',
                'field' => 'slug',
                'terms' => 'school-of-medicine',
            )
        ),
    ) );    
    $som_projects = $the_query -> posts;
    // NEW: make array of the post IDs in one step
    $som_projects_ids = wp_list_pluck( $som_projects, 'ID' );
    // get the terms
    $my_terms = wp_get_object_terms( $som_projects_ids, 'department' );

    return $my_terms;
}

add_action('rest_api_init', function () {
    register_rest_route('tu-research-theme/v1', '/som-departments/', array(
        'methods' => 'GET',
        'callback' => 'get_som_departments',
        'permission_callback' => '__return_true',
    ));
});

