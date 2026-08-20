<?php

/**
 * Single post sidebar widget area (Figma node 180:6683).
 *
 * Contains:
 *   1. "In This Article" table of contents (auto-derived from H2s in the post content)
 *   2. "Protect Your Paint" dark CTA card with gold booking button
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$postId = get_the_ID();

// Extract headings from post content for Table of Contents
$content = get_post_field('post_content', $postId);
$headings = [];

if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', (string) $content, $matches, PREG_SET_ORDER)) {
   foreach ($matches as $match) {
      $text = trim(strip_tags($match[1]));
      if ($text !== '') {
         $slug = sanitize_title($text);
         $headings[] = [
            'text' => $text,
            'slug' => $slug,
         ];
      }
   }
}

// Sidebar CTA field overrides
$ctaTitle = function_exists('get_field') ? get_field('sidebar_cta_title', $postId) : '';
$ctaTitle = !empty($ctaTitle) ? (string) $ctaTitle : __('Protect Your Paint', 'detailking');

$ctaText = function_exists('get_field') ? get_field('sidebar_cta_text', $postId) : '';
$ctaText = !empty($ctaText) ? (string) $ctaText : __('Ready for a coating that lasts years, not weeks? Book your Ceramic Pro with our team.', 'detailking');

$ctaBtnText = function_exists('get_field') ? get_field('sidebar_cta_button_text', $postId) : '';
$ctaBtnText = !empty($ctaBtnText) ? (string) $ctaBtnText : __('Book Ceramic Pro', 'detailking');

$ctaBtnUrl = function_exists('get_field') ? get_field('sidebar_cta_button_url', $postId) : '';
$ctaBtnUrl = !empty($ctaBtnUrl) ? (string) $ctaBtnUrl : home_url('/services/ceramic-pro/');
?>

<aside class="single-sidebar">
   <div class="single-sidebar__sticky">
      <?php if (!empty($headings)) : ?>
         <div class="single-sidebar__card single-sidebar__toc" data-animate="fade-up">
            <h4 class="single-sidebar__title"><?php esc_html_e('In This Article', 'detailking'); ?></h4>
            <nav class="single-sidebar__nav" aria-label="<?php esc_attr_e('Table of contents', 'detailking'); ?>">
               <ul class="single-sidebar__list">
                  <?php foreach ($headings as $heading) : ?>
                     <li class="single-sidebar__item" data-animate>
                        <a href="#<?= esc_attr($heading['slug']); ?>" class="single-sidebar__link">
                           <?= esc_html($heading['text']); ?>
                        </a>
                     </li>
                  <?php endforeach; ?>
               </ul>
            </nav>
         </div>
      <?php endif; ?>

      <div class="single-sidebar__card single-sidebar__cta" data-animate="zoom">
         <h4 class="single-sidebar__cta-title"><?= esc_html($ctaTitle); ?></h4>
         <p class="single-sidebar__cta-text"><?= esc_html($ctaText); ?></p>
         <a href="<?= esc_url($ctaBtnUrl); ?>" class="btn-gold single-sidebar__cta-btn">
            <?= esc_html($ctaBtnText); ?>
         </a>
      </div>
   </div>
</aside>
