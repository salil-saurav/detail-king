<?php

/**
 * The comments template.
 *
 * Note: comments are disabled by default via DebloaterService. Re-enable them
 * with the `detailking/theme/debloater/config` filter (set
 * features.disable_comments to false) — see docs/filter.md.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

if (post_password_required()) {
   return;
}
?>
<div id="comments" class="comments-area mt-5">

   <?php if (have_comments()) : ?>
      <h2 class="comments-title">
         <?php
         $count = get_comments_number();
         printf(
            esc_html(_n('%s Comment', '%s Comments', $count)),
            esc_html(number_format_i18n($count))
         );
         ?>
      </h2>

      <ol class="comment-list">
         <?php
         wp_list_comments([
            'style'      => 'ol',
            'short_ping' => true,
            'avatar_size' => 48,
         ]);
         ?>
      </ol>

      <?php the_comments_pagination(); ?>
   <?php endif; ?>

   <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
      <p class="no-comments"><?php esc_html_e('Comments are closed.'); ?></p>
   <?php endif; ?>

   <?php comment_form(); ?>

</div>
