<?php

/**
 * Template for displaying 404 (not found) pages.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<section class="error-404-section py-5 text-center">
   <div class="container">
      <h1 class="display-4"><?php esc_html_e('404'); ?></h1>
      <h2 class="h4"><?php esc_html_e('Page not found'); ?></h2>
      <p class="text-muted">
         <?php esc_html_e('The page you are looking for may have been moved, renamed, or no longer exists.'); ?>
      </p>

      <div class="my-4">
         <?php get_search_form(); ?>
      </div>

      <a class="btn btn-primary" href="<?= esc_url(home_url('/')); ?>"><?php esc_html_e('Back to Home'); ?></a>
   </div>
</section>

<?php get_footer(); ?>
