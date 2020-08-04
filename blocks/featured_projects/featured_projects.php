<?php
// Featured Projects - hooked into research listings
// See custom_post_types.php and customizer.php


// Markup data
$BLOCK_SLUG = 'projects-container';
$block_class = $BLOCK_SLUG;
$id = $block_class . '-' . $block['id'];
if ( !empty($block['anchor']) )  $id = $block['anchor'];
if ( !empty($block['className']) )  $block_class .= ' ' . $block['className'];
if ( !empty($block['align']) )  $block_class .= ' align' . $block['align'];
$id = esc_attr($id);
$block_class = esc_attr($block_class);

if( get_theme_mod("trp_featured_projects_1") ): ?>
<div id="<?=$id?>" class="<?=$block_class?>">
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