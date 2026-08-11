<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Base class for per-page ACF field groups that are edited directly on a Page's
 * own editor screen — NOT on a global options page.
 *
 * The group binds to a page template via a `page_template` location rule, so
 * its fields show whenever an editor assigns that template to a Page. Values are
 * stored as post meta on that page and read in the template through
 * MetaHelper::field() / ::fieldRows() (current-post context).
 *
 * Adding a new page group is one file: extend this class, point template() at
 * the page template and fill in groupKey()/groupTitle()/fields(). The
 * ServiceLoader auto-discovers every concrete subclass — no manual registration.
 *
 * Example:
 *
 *   class PageMeta_About extends AbstractPageMeta
 *   {
 *      protected function template(): string   { return 'pages/template-about.php'; }
 *      protected function groupKey(): string    { return 'group_about_page'; }
 *      protected function groupTitle(): string  { return __('About Page Content'); }
 *      protected function fields(): array       { return [ $this->field('subtitle', __('Subtitle')) ]; }
 *   }
 */
abstract class AbstractPageMeta extends Singleton implements ServiceInterface
{
   use FieldBuilderTrait;

   /** Template path relative to the theme root, e.g. 'pages/template-about.php'. */
   abstract protected function template(): string;

   /** Unique ACF field-group key, e.g. 'group_detailking_about_page'. */
   abstract protected function groupKey(): string;

   /** Field-group title shown on the page editor meta box. */
   abstract protected function groupTitle(): string;

   /**
    * The ACF field arrays for this page. Build them with the FieldBuilderTrait
    * helpers: $this->tab(), $this->field(), $this->repeater(), $this->imageField().
    *
    * @return array<int,array<string,mixed>>
    */
   abstract protected function fields(): array;

   /**
    * Shared image-field config: store the attachment ID so templates can render
    * it with MetaHelper::imageTag().
    *
    * @return array<string,mixed>
    */
   protected function imageField(): array
   {
      return ['return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all'];
   }

   public function register(): void
   {
      add_action('acf/init', [$this, 'registerFields'], 10);
   }

   /**
    * ACF location rules for this group.
    *
    * Defaults to binding on the page template, which is right for any assignable
    * template. Override it for groups that attach some other way — the homepage
    * is the case in point: front-page.php is chosen by WordPress automatically
    * and is never assigned in the editor, so a `page_template` rule would never
    * match and the group would simply never appear.
    *
    * @return array<int,array<int,array<string,string>>>
    */
   protected function location(): array
   {
      return [[['param' => 'page_template', 'operator' => '==', 'value' => $this->template()]]];
   }

   public function registerFields(): void
   {
      if (!function_exists('acf_add_local_field_group')) {
         return;
      }

      acf_add_local_field_group([
         'key'        => $this->groupKey(),
         'title'      => $this->groupTitle(),
         'fields'     => $this->fields(),
         'location'   => $this->location(),
         'menu_order' => 0,
         'position'   => 'normal',
         'active'     => true,
      ]);
   }
}
