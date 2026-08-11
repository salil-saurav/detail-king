<?php

/**
 * The header: <head> section and everything up to the opening <main>.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) {
   exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
   <meta charset="<?php bloginfo('charset'); ?>">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

   <?php
   wp_body_open();
   get_template_part('template-parts/header/header');
   ?>

   <!--// Main Site Container \\-->
   <main id="site-container" class="layout layout--site">
