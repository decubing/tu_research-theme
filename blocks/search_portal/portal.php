<?php
/**
 * Portal block. A reactive portal for exploring taxonomies.
 * Currently hardcoded to support Categories, Topics, and Schools.
 *
 * ------ standard block params (common to all ACF blocks)
 * @param   array      $block The block settings and attributes.
 * @param   string     $content The block inner HTML (empty).
 * @param   bool       $is_preview True during AJAX preview.
 * @param   int|string $post_id The post ID this block is saved to.
 * 
 * ------ block-level params (defined via ACF fields in the block editor)
 * @param   int        $max_per_page The maximum number of posts to show per page.
 * 
 * ------ instance-level params (can be initialized, but change over an instance's lifecycle)
 * @param   array[]    $portal_filters - array of arrays, first item is the taxonomy, second is the term
 * @param   string     $portal_query
 * @param   int        $current_page
 * @param   int        $total_pages
 * 
 */

// Constants
$BLOCK_SLUG = 'portal';

// Block data
$max_per_page =  (int)get_field('max_count') ?? 8;

// Markup data
$block_class = $BLOCK_SLUG;
$id = $block_class . '-' . $block['id'];
if ( !empty($block['anchor']) )  $id = $block['anchor'];
if ( !empty($block['className']) )  $block_class .= ' ' . $block['className'];
if ( !empty($block['align']) )  $block_class .= ' align' . $block['align'];
$id = esc_attr($id);
$block_class = esc_attr($block_class);

// default instance data
// TODO use query string values if found
$portal_filters = $_REQUEST['portal_filters'] ?? [];
$portal_query = $_REQUEST['portal_query'] ?? '';
$current_page = $_REQUEST['paged'] ?? 1;

/**
 *  Block Query
 * 
 *  From posts
 *  Select title, description, thumbnail, permalink
 *  Where post in selected taxonomies
 *  And search string in post
 *  Paginated
 *  Count total per taxonomy after the query
 */

// Base query
$args = array(
  'post_type'	     => 'research-listing',
  'posts_per_page' => $max_per_page,
  'paged'          => $current_page,
);

$department = "";

// Taxonomy filter
if ( !empty($portal_filters) ) {
  $args['tax_query'] = [];
  foreach ($portal_filters as $filter) {
    [$tax, $slug] = explode('_', $filter);

    if($slug !== 'null'){   
      if($tax == 'department'){
        $department = $slug;
      }
      if($department !== 'school-of-medicine'){
        $args['tax_query'][] = array(
          'taxonomy' => 'department',
          'field'    => 'slug',
          'terms'    => 'school-of-medicine',
          'operator' => 'NOT IN',
        );
      }else{
        $args['tax_query'][] = array(
          'taxonomy' => 'school',
          'field'    => 'slug',
          'terms'    => 'school-of-medicine',
          'operator' => 'IN',
        );
      }
      $args['tax_query'][] = array(
        'taxonomy' => $tax,
        'field'    => 'slug',
        'terms'    => $slug,
        'operator' => 'AND',
      );
    }
  }
}

// Full text search
if ( $portal_query ) {
  $args['s'] = $portal_query;
}

// Secondary search without pagination
// Used to get accurate counts
// Very similar to the base query
// PHP always copies arrays by value
$tax_filter_args = $args;
$tax_filter_args['posts_per_page'] = -1;
unset($tax_filter_args['paged']);

// Run queries
$query = new WP_Query( $args );
$tax_filter_query = new WP_Query( $tax_filter_args );

// TODO
// Count up reults per taxonomy using tax_filter_query
// Each element of the form: 'taxonomy' => ['term' => count]
// Get terms, group them by taxonomy, and tally the results up
if( $department == "school-of-medicine" ){
  $taxonomy_counts = [
    'category' => [], 
    'topic' => [], 
  ];
}else{
$taxonomy_counts = [
  'category' => [], 
  'topic' => [], 
  'school' => [],
];
}

if ($tax_filter_query->have_posts()) {
  // Iterate through found posts
  while ($tax_filter_query->have_posts()) {
    $tax_filter_query->the_post();
    // Iterate through taxonomies of a post - save terms in-place
    foreach ($taxonomy_counts as $taxonomy => &$terms) {
      $tax_terms = get_the_terms(get_the_ID(), $taxonomy);
      // Any terms found?
      if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
        // Iterate through terms in a taxonomy
        foreach ($tax_terms as $term) {
          // Initialize term count if needed
          if (!array_key_exists($term->slug, $terms))
            $terms[$term->slug] = 0;
          // Increment term count
          $terms[$term->slug]++;
        }
      }
    }
  } 
  wp_reset_query();
}

/**
 * Markup outline
 * Two columns on desktop
 * Left column:
 *   Filter list 
 * Right column: 
 *   Top searchbar
 *   Result list
 *   Pagination
 * 
 * Stacked on mobile
 * Left column over right column
 */

?>

<div id="<?=$id?>" class="<?=$block_class?>">
  <div class="portal-filters">
    <?php foreach ($taxonomy_counts as $taxonomy => $term_counts): ?>
    <div class="filters-filter_group active">
      <button class="filter_group-toggle"><?=$taxonomy?></button>
      <ul class="filter_group-<?=$taxonomy?>_filters">
        <?php foreach ($term_counts as $term => $count): ?>
          <?php
            $term_param = $taxonomy . '_' . $term;
            $param_q_string = "&portal_filters%5B%5D={$term_param}";
            // note the \d* to match the digits in between the url-encoded brackets
            $param_q_regex = "/\?portal_filters%5B0%5D={$term_param}/";
            
            // Filter active?
            $active_class = in_array($term_param, $portal_filters) 
              ? ' active'
              : '';
            // Build link - depends on if is active or not
            $url = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            if ($active_class) {
              /* echo "<pre>";
              echo $url;
              echo "</pre>"; */
              $url = preg_replace($param_q_regex, '', $url); 
              /* echo "<pre>";
              echo $url;
              echo "</pre>"; */
            } else {
              $url .= $param_q_string;
            }
            // Get nice name for term
            $term_nice_name = get_term_by('slug', $term, $taxonomy)->name ?? $term;

            ?>
          <li class="<?=$taxonomy?>_filters-the_filter portal-queryable<?=$active_class?>">
            <a class="the_filter-link" href="<?=$url?>"><?=$term_nice_name?> (<?=$count?>)</a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="portal-content">
    <div class="content-search">
      <form method="get" class="search-form">
        <input type="text" class="form-input" name="portal_query" value="<?=$portal_query?>" placeholder="Search..">
        <?php if (!empty($portal_filters)): foreach ($portal_filters as $p_filter): ?>
          <input type="hidden" name="portal_filters[]" value="<?=$p_filter?>" />
        <?php endforeach; endif; ?>
        <input type="hidden" name="paged" value="1" />
        <?php if (isset($_REQUEST['page_id'])): // fix for non-permalinked pages ?>
        <input type="hidden" name="page_id" value="<?=$_REQUEST['page_id']?>">
        <?php endif; ?>
        <input class="form-submit" type="submit" value="Search">
      </form>
    </div>
    <?php if( $query->have_posts() ): ?>
      <ul class="content-results_list">
        <?php while( $query->have_posts() ) : $query->the_post(); ?>
          <?php
          $thumbnail = get_the_post_thumbnail(get_the_ID(), 'thumbnail');
          $title = get_the_title();
          $excerpt = get_the_excerpt();
          $categories = wp_get_post_categories(get_the_ID(), ['fields' => 'all']);
          $permalink = get_the_permalink();
          ?>
          <li class='results_list-item'>
            <div class="item-image">
              <?=$thumbnail?>
            </div>
            <div class="item-info">
              <div class="info-title"><a href="<?=$permalink?>"><?=$title?></a></div>
              <div class="info-excerpt"><?=$excerpt?></div>
              <div class="info-categories">
                <ul class="post-categories">
                  <?php foreach ($categories as $cat) {
                    // wipe filters and set new filter for this category
                    $slug = $cat->slug;
                    $name = $cat->name;
                    $url = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
                    $wipe_filter_regex = "/&portal_filters%5B%5D=.*/";
                    $url = preg_replace($wipe_filter_regex, '', $url);
                    $url .= "&portal_filters%5B%5D=category_{$slug}";
                    echo "<li><a href=\"$url\">$name</a></li>\n";
                  } ?>
                </ul>
              </div>
            </div>
          </li>
        <?php endwhile; ?>
      </ul>
      <div class="content-pagination">
        <?php
          // Haha look at this stupid pagination fix because WordPress uses global magic and treats custom loops like seventh class citizens
          // Kinda wanna make a pull request to add this to WP_Query's methods or something. idk
          global $wp_query;
          $temp_query = $wp_query;
          $wp_query = $query;
          the_posts_pagination();
          $wp_query = $temp_query;
        ?>
      </div>
      <?php else: ?>
        <div class="portal-no_results">No results found. Please try a different query.</div>    
      <?php endif; ?>
    <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>
  </div>
</div>
<?php
