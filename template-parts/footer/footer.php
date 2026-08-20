<?php

/**
 * Site footer — the comp's dark 4-column footer with the outlined wordmark.
 *
 * Columns: brand + blurb + socials / Quick Links / Services / Contact.
 * The two link columns are WP nav menus rather than fields, because they are
 * navigation and an editor will expect to manage them in Appearance → Menus.
 * When a location has no menu assigned the column is skipped entirely rather
 * than rendering an empty heading.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$brand     = (string) $meta->optOr('header_brand_name');
$about     = (string) $meta->optOr('footer_about_text');
$watermark = (string) $meta->optOr('footer_watermark');
$copyright = (string) $meta->optOr('footer_copyright');
$legal     = (string) $meta->optOr('footer_legal_text');

$address = (string) $meta->optOr('contact_address');
$phone   = (string) $meta->optOr('contact_phone');
$email   = (string) $meta->optOr('contact_email');
$hours   = (string) $meta->optOr('contact_hours');

$socials = $meta->rowsOr('footer_social_links');

$logoFallback = get_template_directory_uri() . '/assets/images/brand/logo.png';
$logo = $meta->optImageTag('header_logo', 'full', [
   'class' => 'dk-footer__logo',
   'alt'   => $brand,
], $logoFallback);

/**
 * Footer link columns. Heading text lives here rather than in a field because it
 * labels a *menu location* — renaming it without changing the menu would be a
 * lie, and the menu itself is where an editor works.
 */
$linkColumns = [
   'footer-quick'    => __('Quick Links', 'detailking'),
   'footer-services' => __('Services', 'detailking'),
];
?>
<?php /* No data-animate here: the footer is chrome, it is the last thing on the
        page, and the comp does not fade it in. Revealing it only risks the
        whole footer sitting at opacity 0 if the observer never fires. */ ?>
<footer id="site-footer" class="dk-footer section--dark">
   <div class="dk-footer__inner">

      <div class="dk-footer__cols">

         <!-- Brand column -->
         <div class="dk-footer__brand" data-animate="fade-left">
            <a class="dk-footer__brandlink" href="<?= esc_url(home_url('/')); ?>" aria-label="<?= esc_attr($brand); ?>">
               <?= $logo; ?>
            </a>

            <?php if ($about !== '') : ?>
               <p class="dk-footer__about"><?= esc_html($about); ?></p>
            <?php endif; ?>

            <?php if ($socials) : ?>
               <ul class="dk-socials">
                  <?php foreach ($socials as $row) :
                     $url   = (string) ($row['social_url'] ?? '');
                     $label = (string) ($row['social_label'] ?? '');
                     $icon  = (string) ($row['social_icon'] ?? '');
                     if ($url === '') {
                        continue;
                     }
                  ?>
                     <li>
                        <a class="dk-socials__link" href="<?= esc_url($url); ?>"
                           aria-label="<?= esc_attr($label); ?>"
                           target="_blank" rel="noopener noreferrer">
                           <?php get_template_part('template-parts/components/social-icon', null, ['icon' => $icon]); ?>
                        </a>
                     </li>
                  <?php endforeach; ?>
               </ul>
            <?php endif; ?>
         </div>

         <!-- Link columns -->
         <?php foreach ($linkColumns as $location => $heading) : ?>
            <?php if (!has_nav_menu($location)) {
               continue;
            } ?>
            <nav class="dk-footer__col" aria-label="<?= esc_attr($heading); ?>">
               <h2 class="dk-footer__heading label-md" data-animate="fade-left"><?= esc_html($heading); ?></h2>
               <?php
               wp_nav_menu([
                  'theme_location' => $location,
                  'container'      => false,
                  'menu_class'     => 'dk-footer__menu',
                  'depth'          => 1,
                  'fallback_cb'    => false,
               ]);
               ?>
            </nav>
         <?php endforeach; ?>

         <!-- Contact column -->
         <div class="dk-footer__col">
            <h2 class="dk-footer__heading label-md" data-animate="fade-left"><?php esc_html_e('Contact', 'detailking'); ?></h2>
            <ul class="dk-footer__contact">
               <?php if ($address !== '') : ?>
                  <li>
                     <span class="dk-footer__icon" aria-hidden="true">
                        <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'building']); ?>
                     </span>
                     <span><?= esc_html($address); ?></span>
                  </li>
               <?php endif; ?>

               <?php if ($phone !== '') : ?>
                  <li>
                     <span class="dk-footer__icon" aria-hidden="true">
                        <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'phone']); ?>
                     </span>
                     <a href="tel:<?= esc_attr(preg_replace('/[^\d+]/', '', $phone)); ?>"><?= esc_html($phone); ?></a>
                  </li>
               <?php endif; ?>

               <?php if ($email !== '') : ?>
                  <li>
                     <span class="dk-footer__icon" aria-hidden="true">
                        <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'mail']); ?>
                     </span>
                     <a href="mailto:<?= esc_attr($email); ?>"><?= esc_html($email); ?></a>
                  </li>
               <?php endif; ?>

               <?php if ($hours !== '') : ?>
                  <li>
                     <span class="dk-footer__icon" aria-hidden="true">
                        <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'clock']); ?>
                     </span>
                     <span><?= esc_html($hours); ?></span>
                  </li>
               <?php endif; ?>
            </ul>
         </div>

      </div>

      <?php if ($watermark !== '') : ?>
         <div class="dk-footer__watermark" aria-hidden="true"><?= esc_html($watermark); ?></div>
      <?php endif; ?>

      <div class="dk-footer__bar">
         <p class="dk-footer__copy"><?= esc_html(str_replace('{year}', wp_date('Y') ?: gmdate('Y'), $copyright)); ?></p>

         <?php if (has_nav_menu('footer-legal')) : ?>
            <?php
            wp_nav_menu([
               'theme_location' => 'footer-legal',
               'container'      => false,
               'menu_class'     => 'dk-footer__legalmenu',
               'depth'          => 1,
               'fallback_cb'    => false,
            ]);
            ?>
         <?php elseif ($legal !== '') : ?>
            <p class="dk-footer__legal"><?= esc_html($legal); ?></p>
         <?php endif; ?>
      </div>

   </div>
</footer>
