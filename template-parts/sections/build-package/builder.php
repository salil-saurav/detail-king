<?php

/**
 * Build Your Package — Main Builder Layout.
 *
 * Wraps Section 1 (Vehicle), Section 2 (Service), Section 3 (Your Details)
 * and the Package Summary Sidebar in a single section and form wrapper.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<section class="byop-builder-section section-padding-block pt-0">
   <div class="container-dk">
      <form id="byop-form" class="byop-form" novalidate>
         <!-- Honeypot -->
         <input type="text" name="dk_hp" style="display:none !important" tabindex="-1" autocomplete="off">
         
         <div class="byop-layout">
            <div class="byop-layout__main">
               <?php get_template_part('template-parts/sections/build-package/vehicle'); ?>
               <?php get_template_part('template-parts/sections/build-package/service'); ?>
               <?php get_template_part('template-parts/sections/build-package/details'); ?>
            </div>
            <aside class="byop-layout__sidebar">
               <?php get_template_part('template-parts/sections/build-package/summary'); ?>
            </aside>
         </div>
      </form>
   </div>
</section>
