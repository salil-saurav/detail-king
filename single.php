<?php

/**
 * Single Blog Post Template.
 *
 * Implements Figma node 180:6582 ("Blog Inner").
 * Bands:
 *   1. Single Hero (template-parts/sections/blog/single-hero)
 *   2. Main Article + Sidebar (TOC, CTA card)
 *   3. Related Articles (template-parts/sections/blog/single-related)
 *   4. CTA Banner (template-parts/global/cta-banner)
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) :
   the_post();

   $postId = get_the_ID();
   $tags   = get_the_tags($postId);
   $permalink = get_permalink($postId);
   $title     = get_the_title($postId);

   // Single Hero
   get_template_part('template-parts/sections/blog/single-hero');
   ?>

   <section class="single-body">
      <div class="container-dk">
         <div class="single-layout">

            <main class="single-layout__main">
               <article <?php post_class('single-article-content'); ?>>
                  <?php
                  // Output content, ensuring H2 tags carry id attributes for TOC jump links
                  $content = get_the_content();
                  $content = apply_filters('the_content', $content);

                  // Inject IDs into H2s if they don't already have them
                  $content = preg_replace_callback('/<h2([^>]*)>(.*?)<\/h2>/is', function ($matches) {
                     $attrs = $matches[1];
                     $text  = $matches[2];
                     if (strpos($attrs, 'id=') !== false) {
                        return $matches[0];
                     }
                     $slug = sanitize_title(strip_tags($text));
                     return '<h2 id="' . esc_attr($slug) . '"' . $attrs . '>' . $text . '</h2>';
                  }, $content);

                  echo $content;
                  ?>

                  <footer class="single-article-footer">
                     <?php if ($tags && !is_wp_error($tags)) : ?>
                        <div class="single-article-tags">
                           <?php foreach ($tags as $tag) : ?>
                              <a href="<?= esc_url(get_tag_link($tag->term_id)); ?>" class="single-tag-pill">
                                 <?= esc_html($tag->name); ?>
                              </a>
                           <?php endforeach; ?>
                        </div>
                     <?php endif; ?>

                     <div class="single-share-bar">
                        <span class="single-share-bar__label"><?php esc_html_e('Share', 'detailking'); ?></span>
                        <div class="single-share-bar__links">
                           <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($permalink); ?>"
                              class="single-share-btn"
                              target="_blank"
                              rel="noopener noreferrer"
                              aria-label="<?php esc_attr_e('Share on Facebook', 'detailking'); ?>">
                              <span aria-hidden="true">f</span>
                           </a>
                           <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($permalink); ?>&text=<?= rawurlencode($title); ?>"
                              class="single-share-btn"
                              target="_blank"
                              rel="noopener noreferrer"
                              aria-label="<?php esc_attr_e('Share on X', 'detailking'); ?>">
                              <span aria-hidden="true">𝕏</span>
                           </a>
                           <button type="button"
                              class="single-share-btn"
                              data-dk-copy-url="<?= esc_url($permalink); ?>"
                              aria-label="<?php esc_attr_e('Copy article link', 'detailking'); ?>"
                              title="<?php esc_attr_e('Copy Link', 'detailking'); ?>">
                              <span aria-hidden="true">⧉</span>
                           </button>
                        </div>
                     </div>
                  </footer>
               </article>
            </main>

            <?php get_template_part('template-parts/sections/blog/single-sidebar'); ?>

         </div>
      </div>
   </section>

   <?php
   // Related Articles
   get_template_part('template-parts/sections/blog/single-related');
   ?>

   <section class="single-cta">
      <div class="container-dk">
         <?php
         $blogPageId = (int) get_option('page_for_posts');
         $blogUrl    = $blogPageId ? get_permalink($blogPageId) : home_url('/blog/');

         get_template_part('template-parts/global/cta-banner', null, [
            'title'          => 'Ready To Treat',
            'gold'           => 'Your Car?',
            'text'           => 'Reading is great — results are better. Book a service or talk to our team today.',
            'primary_text'   => 'Book Now',
            'primary_url'    => home_url('/contact/'),
            'secondary_text' => 'Back to Blog',
            'secondary_url'  => $blogUrl,
         ]);
         ?>
      </div>
   </section>

<?php
endwhile;
?>

<script>
(() => {
   document.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-dk-copy-url]');
      if (!btn) return;
      const url = btn.getAttribute('data-dk-copy-url');
      if (url && navigator.clipboard) {
         navigator.clipboard.writeText(url).then(() => {
            const orig = btn.getAttribute('title') || 'Copy Link';
            btn.setAttribute('title', 'Copied!');
            btn.classList.add('is-copied');
            setTimeout(() => {
               btn.setAttribute('title', orig);
               btn.classList.remove('is-copied');
            }, 2000);
         }).catch(() => {});
      }
   });
})();
</script>

<?php
get_footer();
