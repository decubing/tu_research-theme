<?php
// =====================================================================
// SINGLE CONTENT LAYOUT
// =====================================================================
// This layout displays single content items, like posts or pages.
// =====================================================================

// Begin Single Content Layout
if( is_single() || is_singular() ): 
?>

<div class="layout-single">

  <?php 
  // Begin Loop
  if(have_posts()): while(have_posts()): the_post();
  ?>

  <div <?php post_class( 'single-loop' ); ?> id="post-<?php the_ID(); ?>">

    <?php
    // Optional Title
    if(!get_field('hide_title'))
      the_title('<div class="loop-the_title">', '</div>', true);
    ?>

    <div class="loop-the_content">
      <?php
      // Display author name and department
      if ( 'research-listing' == get_post_type() ) {

          $faculty_name = get_field('faculty_name') ?? "";
          $school = get_field('school') ?? "";

          $author_line = '';
          $seperator = ", ";

          if(trim($faculty_name) !== "" && trim($school) !== ""){
            $author_line = $faculty_name.$seperator.$school;
          }else{
            $author_line.=$faculty_name.$school;
          }


          if ( $author_line ) {
              printf('<p style="font-size: 1.1em;">%s</>', $author_line);
          }
      }
      ?>
      <?php
      // The Content
      the_content();
      ?>
      
    

    <?php

    // add form for School of Medicine posts
    if (has_term('school-of-medicine', 'school')) {
      echo "<!-- Project Signup area -->";
      //echo do_shortcode('[private role="visitor-only"]To apply for an opportunity, <a href="https://researchnetwork.tulane.edu/wp-admin">click here</a> to login with your Tulane student ID.[/private][private role="custom" custom_role="student"][gravityform id="4" title="false" description="false"][/private]');
       /*  echo '<div class="wp-block-group" style="background: var(--wp--preset--color--gray-250); padding: 0em 2em 2em 2em; border-top:2px solid">';
        echo '<h3>Apply For This Opportunity</h3>';
        echo do_shortcode('[gravityform id="2" title="false" description="false"]');
        echo '</div>'; */
      
    }
    ?>
    </div>
    <?php  
    // Post Meta
    if( is_single('post') ):
    ?>

    <div class="loop-meta">

      <?php
      // Author
      if( get_the_author() )
        echo '<span class="screen-reader-text">Posted by</span> <a class="meta-author" href="' . get_the_author_link() . '"><svg class="author-icon" width="16" height="16" aria-hidden="true" role="img" focusable="false" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path><path d="M0 0h24v24H0z" fill="none"></path></svg>' . get_the_author() . '</a>';

      // Date
      if( get_the_date() )
        echo '<span class="screen-reader-text">on</span> <span class="meta-date"><svg class="date-icon" width="16" height="16" aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><defs><path id="a" d="M0 0h24v24H0V0z"></path></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"></use></clipPath><path clip-path="url(#b)" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"></path></svg>' . get_the_date() . '</span>';

      // Categories
      $categories = array();
      foreach(  get_categories() as $category ) {
        $categories[]= '<a class="categories-the_category" href="' . get_category_link($category->term_id) . '">' . $category->name . '</a>';
      }
      if ( !empty($categories) ) {
        echo '<span class="screen-reader-text">and categorized as</span> <span class="meta-categories"> <svg class="categories-icon" width="16" height="16" aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"></path><path d="M0 0h24v24H0z" fill="none"></path></svg>';
        print implode(', ', $categories);
        echo '</span>';
      }

      ?>
      
    </div>

    <?php
    // End Post Meta
    endif;
    ?>

  </div>

  <?php
  // End Single Content Layout
  endwhile;

  // Error Fallback
  else:
  ?>

  <div class="single-error">
    <div class="error-title">Sorry!</div>
    <div class="error-body">No content exists.</div>
  </div>
  
  <?php
  // End Loop
  endif;
  ?>

</div>

<?php
// Featured Projects - hooked into research listings
// See custom_post_types.php and customizer.php
if( is_singular('research-listing') && get_theme_mod("trp_featured_projects_1") ): ?>
  <div class="projects-container">
    <h3 class="projects-header">Featured Projects</h3>
    <div class="projects-card_container">
    <?php foreach ([1,2,3] as $num): $feature_id = (int)get_theme_mod("trp_featured_projects_$num"); ?>
      <?php if ( $feature_id ): 
        $image = get_the_post_thumbnail($feature_id, 'thumbnail');
        $category = get_the_category($feature_id);
        $link = get_the_permalink($feature_id);
        if (!empty($category)) $category = $category[0]->name;
        $title = get_the_title($feature_id);
        $excerpt = wp_trim_words(get_the_excerpt($feature_id), 7);
        ?>
          <div class="projects-card">
            <a href="<?=$link?>" class="projects-link">
              <div class="card-image"><?=$image?></div>
              <div class="card-category"><?=$category?></div>
              <div class="card-title"><?=$title?></div>
              <div class="card-excerpt"><?=$excerpt?></div>
            </a>
          </div>
      <?php endif;
    endforeach; ?>
    </div>
  </div>
<?php endif;

// End Single Content Layout
endif;
?>