# Graph Report - detailking  (2026-08-18)

## Corpus Check
- 203 files · ~131,864 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 744 nodes · 994 edges · 139 communities (81 shown, 58 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 26 edges (avg confidence: 0.82)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0c3ef928`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TermMeta_ProductCategory
- FormService
- PostTypeMeta_Booking
- PostTypeMeta_Faq
- LeadPostType
- DebloaterService
- PageMeta_Homepage
- MetaHelper
- ACF Defaults & Field-Builder Infrastructure
- DefaultsProvider
- SMTP Mail Service
- ServiceInterface
- AssetsService
- Singleton
- composer.json
- ThemeService
- SecurityService
- ThemeHelper
- PostTypeService
- MediaUploader
- ShowCurrentTemplate
- GlobalFields
- Application
- MediaConverter
- DetailKing\Theme\Core\ServiceInterface
- AdminCustomizations
- ServiceLoader.php
- WP_REST_Response
- WP_REST_Request
- AuthService
- About Hero Background Image
- Detail King Visual Brand Identity
- MediaConverter
- Detail Brushes Feature
- High Speed Polishing Detailing Feature
- World Class Products Icon
- detailking/theme/admin/footer_text
- Service-Based Architecture
- Professional Equipment Icon
- Car Before/After Comparison
- Studio FAQ Background
- Hero Background Car Polishing
- Custom Build Promo Car
- Seam Polishing Detail
- Video Thumbnail Car in Smoke
- Why Us Car Rear View
- Actions Documentation
- SearchService
- ShowCurrentTemplate
- MembershipAccountService.php
- ProtectionFinderService
- AbstractPageMeta
- PageMeta_About
- PageMeta_Shop
- SearchService
- protection-finder.js
- PageMeta_BuildPackage
- PageMeta_Contact
- PageMeta_Gallery
- PageMeta_Membership
- PageMeta_ProtectionFinder
- PageMeta_Services
- PostTypeMeta_Location
- PageMeta_PageBanner
- PostTypeMeta_Gallery
- PostTypeMeta_Membership
- PostTypeMeta_Package
- PostTypeMeta_Post
- PostTypeMeta_Product
- PostTypeMeta_Service
- PostTypeMeta_Testimonial
- AbstractPostTypeMeta

## God Nodes (most connected - your core abstractions)
1. `Singleton` - 65 edges
2. `MetaHelper` - 26 edges
3. `FormService` - 26 edges
4. `DebloaterService` - 25 edges
5. `AbstractPageMeta` - 24 edges
6. `BookingWidgetService` - 20 edges
7. `AssetsService` - 17 edges
8. `PackageBuilderService` - 17 edges
9. `SmtpService` - 17 edges
10. `CrossSellService` - 16 edges

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

## Communities (139 total, 58 thin omitted)

### Community 8 - "MetaHelper"
Cohesion: 0.11
Nodes (3): MetaHelper, PackageBuilderService, WP_Term

### Community 9 - "ACF Defaults & Field-Builder Infrastructure"
Cohesion: 0.19
Nodes (11): DefaultsRegistry, defaultFor(), defaultsSlug(), field(), fieldKey(), iconItemSubFields(), imageArgs(), keyNamespace() (+3 more)

### Community 10 - "DefaultsProvider"
Cohesion: 0.05
Nodes (13): DefaultsProvider, AboutDefaults, BlogDefaults, BuildPackageDefaults, ContactDefaults, GalleryDefaults, GlobalDefaults, HomepageDefaults (+5 more)

### Community 12 - "ServiceInterface"
Cohesion: 0.14
Nodes (15): detailking/theme/security/block_author_enumeration, detailking/theme/debloater/config, detailking/theme/post_types, detailking/theme/redirects, detailking/theme/security/restrict_rest_users, detailking/theme/security/headers, Application, AssetsService (+7 more)

### Community 14 - "Singleton"
Cohesion: 0.13
Nodes (5): Singleton, SiteContentMenu, DetailKingPostTypes, RedirectService, static

### Community 15 - "composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, config, optimize-autoloader, description, license, name, DetailKing\\Theme\\ (+4 more)

### Community 25 - "DetailKing\Theme\Core\ServiceInterface"
Cohesion: 0.14
Nodes (4): DetailKing\Theme\Core\ServiceInterface, MediaHelper, MenuHelper, TemplateHelper

### Community 28 - "WP_REST_Response"
Cohesion: 0.13
Nodes (5): BookingWidgetService, WP_Post, CrossSellService, WC_Cart, WP_REST_Response

### Community 29 - "WP_REST_Request"
Cohesion: 0.30
Nodes (3): WP_Error, WP_Post, WP_REST_Request

### Community 31 - "About Hero Background Image"
Cohesion: 0.67
Nodes (3): About Us Page Hero Section Styling, About Hero Background Image, Yellow Mercedes-AMG GT Sports Car

### Community 32 - "Detail King Visual Brand Identity"
Cohesion: 1.00
Nodes (3): Detail King Visual Brand Identity, Crown Emblem and Monogram, Detail King Brand Logo Image

### Community 33 - "MediaConverter"
Cohesion: 0.67
Nodes (3): detailking/theme/media/format, MediaConverter, MediaUploader

### Community 92 - "MembershipAccountService.php"
Cohesion: 0.24
Nodes (4): MembershipAccountService, WC_DateTime, WC_Order, WC_Order_Item_Product

### Community 98 - "protection-finder.js"
Cohesion: 0.39
Nodes (6): escapeHtml(), renderResult(), resetWizard(), showQuestion(), submitAnswers(), updateProgress()

## Knowledge Gaps
- **39 isolated node(s):** `name`, `description`, `type`, `license`, `php` (+34 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **58 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `AbstractPageMeta` connect `AbstractPageMeta` to `TermMeta_ProductCategory`, `PageMeta_Shop`, `PageMeta_BuildPackage`, `PageMeta_Contact`, `PageMeta_Gallery`, `PageMeta_Membership`, `PageMeta_Homepage`, `PageMeta_ProtectionFinder`, `PageMeta_Services`, `PageMeta_PageBanner`, `Singleton`, `AbstractPostTypeMeta`, `GlobalFields`, `DetailKing\Theme\Core\ServiceInterface`, `PageMeta_About`?**
  _High betweenness centrality (0.290) - this node is a cross-community bridge._
- **Why does `Singleton` connect `Singleton` to `FormService`, `MetaHelper.php`, `LeadPostType`, `DebloaterService`, `MetaHelper`, `SMTP Mail Service`, `AssetsService`, `ThemeService`, `SecurityService`, `ThemeHelper`, `PostTypeService`, `MediaUploader`, `ShowCurrentTemplate`, `GlobalFields`, `Application`, `MediaConverter`, `DetailKing\Theme\Core\ServiceInterface`, `AdminCustomizations`, `WP_REST_Response`, `WP_REST_Request`, `AuthService`, `MembershipAccountService.php`, `ProtectionFinderService`, `AbstractPageMeta`, `SearchService`?**
  _High betweenness centrality (0.242) - this node is a cross-community bridge._
- **Why does `AbstractPostTypeMeta` connect `AbstractPostTypeMeta` to `PostTypeMeta_Booking`, `PostTypeMeta_Faq`, `PostTypeMeta_Location`, `PostTypeMeta_Gallery`, `PostTypeMeta_Membership`, `PostTypeMeta_Package`, `PostTypeMeta_Post`, `PostTypeMeta_Product`, `PostTypeMeta_Service`, `PostTypeMeta_Testimonial`, `AbstractPageMeta`?**
  _High betweenness centrality (0.139) - this node is a cross-community bridge._
- **Are the 7 inferred relationships involving `MetaHelper` (e.g. with `.checkThrottle()` and `.notifyEnquiry()`) actually correct?**
  _`MetaHelper` has 7 INFERRED edges - model-reasoned connections that need verification._
- **Are the 4 inferred relationships involving `FormService` (e.g. with `.enqueueAssets()` and `.handleEnquiry()`) actually correct?**
  _`FormService` has 4 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _39 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `MetaHelper.php` be split into smaller, more focused modules?**
  _Cohesion score 0.03571428571428571 - nodes in this community are weakly interconnected._