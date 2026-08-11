<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Admin;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Developer aid: shows the currently rendered template file in the front-end
 * admin bar (visible to administrators only).
 */
class ShowCurrentTemplate extends Singleton implements ServiceInterface
{
   private const ADMIN_BAR_ID    = 'detailking-template-path';
   private const DEFAULT_TEMPLATE = 'Unknown';

   public function register(): void
   {
      add_action('admin_bar_menu', [$this, 'showTemplatePath'], 100);
   }

   public function showTemplatePath($admin_bar): void
   {
      if (!$this->shouldDisplay()) {
         return;
      }

      [$filename, $relativePath] = $this->getTemplateInfo();

      $admin_bar->add_node([
         'id'    => self::ADMIN_BAR_ID,
         'title' => sprintf('Template: %s', $filename),
         'href'  => '',
         'meta'  => ['title' => sprintf('Template relative path: %s', $relativePath)],
      ]);

      $admin_bar->add_node([
         'id'     => self::ADMIN_BAR_ID . '-path',
         'parent' => self::ADMIN_BAR_ID,
         'title'  => sprintf('Relative path: %s', $relativePath),
         'href'   => '',
      ]);
   }

   private function shouldDisplay(): bool
   {
      return is_admin_bar_showing() && current_user_can('manage_options') && !is_admin();
   }

   /**
    * @return array{0:string,1:string} [filename, relativePath]
    */
   private function getTemplateInfo(): array
   {
      global $template;

      if (!$template) {
         return [self::DEFAULT_TEMPLATE, self::DEFAULT_TEMPLATE];
      }

      return [basename($template), $this->getRelativePath($template)];
   }

   private function getRelativePath(string $absolutePath): string
   {
      $themeRoot = get_theme_root();

      if (str_starts_with($absolutePath, $themeRoot)) {
         return '...' . str_replace($themeRoot, '', $absolutePath);
      }

      $abspath = untrailingslashit(ABSPATH);

      if (str_starts_with($absolutePath, $abspath)) {
         return str_replace($abspath, '', $absolutePath);
      }

      return $absolutePath;
   }
}
