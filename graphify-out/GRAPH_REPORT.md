# Graph Report - .  (2026-08-14)

## Corpus Check
- 82 files · ~101,760 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 546 nodes · 718 edges · 92 communities (57 shown, 35 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 15 edges (avg confidence: 0.84)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- ACF Field Groups — Pages & Terms
- Booking & Form Submission Services
- ACF Field Groups — Post Types (Booking/Package/Product/Service)
- ACF Field Groups — Faq/Membership/Testimonial
- Search & Lead Capture
- Performance/Bloat Cleanup Service
- ACF Field Groups — Homepage
- ACF Field Read Helper (MetaHelper)
- ACF Defaults & Field-Builder Infrastructure
- Page Defaults Providers
- SMTP Mail Service
- Theme Documentation & Filter Hooks
- Asset Enqueue Service
- Core Bootstrap & Post Type Registration
- Composer Package Config
- Theme Core Services Overview
- Security Hardening Service
- Breadcrumb & Page Banner Helpers
- Custom Post Type Registration Service
- Media Upload Service
- Admin Template Debug Tool
- Global ACF Fields
- Theme Bootstrap Entry Point
- AVIF/WebP Media Converter
- Nav Menu Helper
- Admin Footer/Assets Customization
- Service Loader Config
- Image Helper
- Menu Fallback Helper
- Content Menu Registration
- About Hero Background Image
- Brand Logo Assets
- Media Format Filter Hook
- Detail Brushes Icon
- High-Speed Polishing Icon
- World-Class Products Icon
- Admin Footer Text Filter
- StackPress Architecture Concept
- Pro Equipment Icon
- Home Before/After Image
- Home FAQ Studio Image
- Home Hero Background Image
- Home Custom-Build Promo Image
- Home Seam Polish Image
- Home Video Thumbnail
- Home Why-Us Car Image
- Actions Documentation
- Search Service (README ref)
- Show-Current-Template (README ref)

## God Nodes (most connected - your core abstractions)
1. `Singleton` - 41 edges
2. `DebloaterService` - 24 edges
3. `FormService` - 22 edges
4. `MetaHelper` - 20 edges
5. `BookingWidgetService` - 20 edges
6. `SmtpService` - 17 edges
7. `AssetsService` - 17 edges
8. `AbstractPageMeta` - 14 edges
9. `LeadPostType` - 12 edges
10. `PageMeta_Homepage` - 11 edges

## Surprising Connections (you probably didn't know these)
- `detailking/theme/debloater/config` --references--> `DebloaterService`  [EXTRACTED]
  docs/filter.md → README.md
- `detailking/theme/security/block_author_enumeration` --references--> `SecurityService`  [EXTRACTED]
  docs/filter.md → README.md
- `detailking/theme/security/restrict_rest_users` --references--> `SecurityService`  [EXTRACTED]
  docs/filter.md → README.md
- `detailking/theme/security/headers` --references--> `SecurityService`  [EXTRACTED]
  docs/filter.md → README.md
- `detailking/theme/media/format` --references--> `MediaConverter`  [EXTRACTED]
  docs/filter.md → README.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Security Hardening Filters** — docs_filter_security_headers_filter, docs_filter_block_author_enumeration_filter, docs_filter_restrict_rest_users_filter [INFERRED 0.85]
- **Service Bootstrapping Architecture Flow** — readme_application, readme_serviceloader, readme_serviceinterface [EXTRACTED 1.00]
- **Detail King Logo Visual Brand Composition** — assets_images_brand_logo_logo, assets_images_brand_logo_crown_symbol, assets_images_brand_logo_brand_identity [EXTRACTED 1.00]

## Communities (92 total, 35 thin omitted)

### Community 0 - "ACF Field Groups — Pages & Terms"
Cohesion: 0.05
Nodes (7): AbstractPageMeta, AbstractTermMeta, PageMeta_About, PageMeta_PageBanner, PageMeta_Services, PageMeta_Shop, TermMeta_ProductCategory

### Community 1 - "Booking & Form Submission Services"
Cohesion: 0.11
Nodes (8): BookingWidgetService, FormService, WC_Order, WC_Order_Item_Product, WP_Error, WP_Post, WP_REST_Request, WP_REST_Response

### Community 3 - "ACF Field Groups — Post Types (Booking/Package/Product/Service)"
Cohesion: 0.07
Nodes (5): AbstractPostTypeMeta, PostTypeMeta_Booking, PostTypeMeta_Package, PostTypeMeta_Product, PostTypeMeta_Service

### Community 4 - "ACF Field Groups — Faq/Membership/Testimonial"
Cohesion: 0.08
Nodes (4): AbstractPostTypeMeta, PostTypeMeta_Faq, PostTypeMeta_Membership, PostTypeMeta_Testimonial

### Community 5 - "Search & Lead Capture"
Cohesion: 0.09
Nodes (4): SearchService, FormRegistry, LeadPostType, WP_Query

### Community 9 - "ACF Defaults & Field-Builder Infrastructure"
Cohesion: 0.19
Nodes (11): DefaultsRegistry, defaultFor(), defaultsSlug(), field(), fieldKey(), iconItemSubFields(), imageArgs(), keyNamespace() (+3 more)

### Community 10 - "Page Defaults Providers"
Cohesion: 0.12
Nodes (6): DefaultsProvider, AboutDefaults, GlobalDefaults, HomepageDefaults, ServicesDefaults, ShopDefaults

### Community 12 - "Theme Documentation & Filter Hooks"
Cohesion: 0.14
Nodes (15): detailking/theme/security/block_author_enumeration, detailking/theme/debloater/config, detailking/theme/post_types, detailking/theme/redirects, detailking/theme/security/restrict_rest_users, detailking/theme/security/headers, Application, AssetsService (+7 more)

### Community 14 - "Core Bootstrap & Post Type Registration"
Cohesion: 0.16
Nodes (4): Singleton, DetailKingPostTypes, RedirectService, static

### Community 15 - "Composer Package Config"
Cohesion: 0.15
Nodes (12): autoload, psr-4, config, optimize-autoloader, description, license, name, DetailKing\\Theme\\ (+4 more)

### Community 16 - "Theme Core Services Overview"
Cohesion: 0.26
Nodes (3): DetailKing\Theme\Core\ServiceInterface, DetailKing\Theme\Core\Singleton, ThemeService

### Community 31 - "About Hero Background Image"
Cohesion: 0.67
Nodes (3): About Us Page Hero Section Styling, About Hero Background Image, Yellow Mercedes-AMG GT Sports Car

### Community 32 - "Brand Logo Assets"
Cohesion: 1.00
Nodes (3): Detail King Visual Brand Identity, Crown Emblem and Monogram, Detail King Brand Logo Image

### Community 33 - "Media Format Filter Hook"
Cohesion: 0.67
Nodes (3): detailking/theme/media/format, MediaConverter, MediaUploader

## Knowledge Gaps
- **39 isolated node(s):** `name`, `description`, `type`, `license`, `php` (+34 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **35 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Singleton` connect `Core Bootstrap & Post Type Registration` to `Booking & Form Submission Services`, `Template Sections & Page Layout`, `Search & Lead Capture`, `ACF Field Groups — Homepage`, `ACF Field Read Helper (MetaHelper)`, `SMTP Mail Service`, `Security Hardening Service`, `Custom Post Type Registration Service`, `Media Upload Service`, `Admin Template Debug Tool`, `Theme Bootstrap Entry Point`, `AVIF/WebP Media Converter`, `Nav Menu Helper`, `Admin Footer/Assets Customization`, `Image Helper`, `Menu Fallback Helper`, `Content Menu Registration`?**
  _High betweenness centrality (0.109) - this node is a cross-community bridge._
- **Why does `AbstractPageMeta` connect `ACF Field Groups — Homepage` to `Theme Core Services Overview`, `ACF Field Groups — Faq/Membership/Testimonial`, `Global ACF Fields`, `Core Bootstrap & Post Type Registration`?**
  _High betweenness centrality (0.092) - this node is a cross-community bridge._
- **Why does `AbstractPostTypeMeta` connect `ACF Field Groups — Faq/Membership/Testimonial` to `ACF Field Groups — Homepage`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _39 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `ACF Field Groups — Pages & Terms` be split into smaller, more focused modules?**
  _Cohesion score 0.04541062801932367 - nodes in this community are weakly interconnected._
- **Should `Booking & Form Submission Services` be split into smaller, more focused modules?**
  _Cohesion score 0.1111111111111111 - nodes in this community are weakly interconnected._
- **Should `Template Sections & Page Layout` be split into smaller, more focused modules?**
  _Cohesion score 0.05128205128205128 - nodes in this community are weakly interconnected._