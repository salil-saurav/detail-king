<?php

/**
 * Blog Index Template (the Posts Page).
 *
 * Implements Figma node 180:6257 ("The Blog").
 * Bands:
 *   1. Hero Banner (template-parts/global/page-banner)
 *   2. Blog Listing Section (category pills, featured post card, 4-column post grid, pagination)
 *   3. CTA Banner (template-parts/global/cta-banner)
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$blogPageId = (int) get_option('page_for_posts');

// Banner fields
$bannerEyebrow = function_exists('get_field') ? get_field('hero_eyebrow', $blogPageId) : '';
$bannerEyebrow = !empty($bannerEyebrow) ? (string) $bannerEyebrow : 'THE DETAIL KING JOURNAL';

$bannerTitle = function_exists('get_field') ? get_field('hero_title', $blogPageId) : '';
$bannerTitle = !empty($bannerTitle) ? (string) $bannerTitle : 'The';

$bannerText = function_exists('get_field') ? get_field('hero_text', $blogPageId) : '';
$bannerText = !empty($bannerText) ? (string) $bannerText : __('Tips, guides and insights from the team — paint care, protection, detailing know-how and everything in between.', 'detailking');

$bannerBgId  = function_exists('get_field') ? get_field('hero_bg_image', $blogPageId) : 0;
$bannerBgUrl = $bannerBgId ? wp_get_attachment_image_url((int) $bannerBgId, 'full') : '';

// Hero Banner
get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => $bannerEyebrow,
   'title'   => $bannerTitle,
   'gold'    => 'Blog',
   'text'    => $bannerText,
   'bg'      => $bannerBgUrl,
   'break'   => false,
   'breadcrumb_suffix' => ['Blog' => ''],
]);

// Categories for filter tabs
$categories = get_categories([
   'hide_empty' => true,
   'orderby'    => 'name',
   'order'      => 'ASC',
]);
?>

<section class="blog-listing">
   <div class="container-dk">

      <?php if (!empty($categories)) : ?>
         <div class="blog-filter-bar" data-dk-filter-group data-dk-filter-mode="hide" data-dk-filter-target=".blog-listing">
            <button type="button"
               class="blog-filter-pill is-active"
               data-dk-filter="all"
               aria-pressed="true">
               <?php esc_html_e('All Posts', 'detailking'); ?>
            </button>
            <?php foreach ($categories as $cat) : ?>
               <button type="button"
                  class="blog-filter-pill"
                  data-dk-filter="<?= esc_attr($cat->slug); ?>"
                  aria-pressed="false">
                  <?= esc_html($cat->name); ?>
               </button>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>

      <?php if (have_posts()) : ?>
         <?php
         // Separate featured/sticky post from regular posts on first page
         $isPaged = is_paged();
         $featuredPost = null;
         $gridPosts = [];

         $allPosts = $GLOBALS['wp_query']->posts;

         if (!$isPaged && !empty($allPosts)) {
            // Check if there's a sticky or featured post
            foreach ($allPosts as $idx => $p) {
               $isSticky = is_sticky($p->ID);
               $isFeatMeta = function_exists('get_field') ? get_field('featured_post', $p->ID) : false;
               if ($isSticky || $isFeatMeta) {
                  $featuredPost = $p;
                  unset($allPosts[$idx]);
                  break;
               }
            }
            // If no explicit sticky, treat first post as featured
            if (!$featuredPost && !empty($allPosts)) {
               $featuredPost = array_shift($allPosts);
            }
            $gridPosts = $allPosts;
         } else {
            $gridPosts = $allPosts;
         }
         ?>

         <?php if ($featuredPost) : ?>
            <div class="blog-featured-wrap">
               <?php
               get_template_part('template-parts/sections/blog/card', null, [
                  'post_id'  => $featuredPost->ID,
                  'featured' => true,
               ]);
               ?>
            </div>
         <?php endif; ?>

         <?php if (!empty($gridPosts)) : ?>
            <div class="blog-grid">
               <?php foreach ($gridPosts as $gridPost) : ?>
                  <?php
                  get_template_part('template-parts/sections/blog/card', null, [
                     'post_id'  => $gridPost->ID,
                     'featured' => false,
                  ]);
                  ?>
               <?php endforeach; ?>
            </div>
         <?php endif; ?>

         <?php
         $pagination = get_the_posts_pagination([
            'mid_size'           => 2,
            'prev_text'          => __('← Previous', 'detailking'),
            'next_text'          => __('Next →', 'detailking'),
            'screen_reader_text' => __('Posts navigation', 'detailking'),
         ]);
         ?>

         <?php if ($pagination) : ?>
            <div class="blog-pagination">
               <?= $pagination; ?>
            </div>
         <?php endif; ?>

      <?php else : ?>
         <div class="blog-empty-state">
            <h3 class="blog-empty-state__title"><?php esc_html_e('No Articles Found', 'detailking'); ?></h3>
            <p class="blog-empty-state__text"><?php esc_html_e('Check back soon for new detailing guides, protection insights, and car care tips.', 'detailking'); ?></p>
            <a href="<?= esc_url(home_url('/')); ?>" class="btn-gold"><?php esc_html_e('Back to Home', 'detailking'); ?></a>
         </div>
      <?php endif; ?>

   </div>
</section>

<section class="blog-cta">
   <div class="container-dk">
      <?php
      get_template_part('template-parts/global/cta-banner', null, [
         'title'          => 'Ready To Treat',
         'gold'           => 'Your Car?',
         'text'           => 'Reading is great — results are better. Book a service or talk to our team today.',
         'primary_text'   => 'Book Now',
         'primary_url'    => home_url('/contact/'),
         'secondary_text' => 'Explore Services',
         'secondary_url'  => home_url('/services/'),
      ]);
      ?>
   </div>
</section>

<?php get_footer(); ?>
