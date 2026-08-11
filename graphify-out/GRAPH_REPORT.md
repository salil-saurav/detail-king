# Graph Report - wp-content/themes/detailking  (2026-08-11)

## Corpus Check
- 95 files · ~75,422 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 374 nodes · 532 edges · 54 communities (33 shown, 21 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 9 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Singleton Base & Helpers
- CPT Field Groups
- Search & Lead Capture
- Form Submission Handling
- Frontend Debloating
- Page Field Groups
- Meta Field Readers
- Defaults Registry & Field Builder
- Asset Enqueueing
- SMTP Mail Settings
- Composer Autoload Config
- Security Hardening
- Breadcrumbs & Page Banner
- Post Type Registration
- Media Upload Sources
- Defaults Providers
- Theme Bootstrap
- Service Loader Config
- Brand Logo & README
- Car Before/After Comparison
- Studio FAQ Background
- Hero Background Car
- Custom Build Promo Car
- Car Polishing Detail
- Video Thumbnail Car
- Why Us Section Car
- Actions Documentation
- Filter Documentation Refs

## God Nodes (most connected - your core abstractions)
1. `Singleton` - 53 edges
2. `FormService` - 23 edges
3. `DebloaterService` - 22 edges
4. `MetaHelper` - 20 edges
5. `AssetsService` - 17 edges
6. `SmtpService` - 17 edges
7. `AbstractPageMeta` - 14 edges
8. `LeadPostType` - 12 edges
9. `PageMeta_Homepage` - 11 edges
10. `FormRegistry` - 11 edges

## Surprising Connections (you probably didn't know these)
- `Detail King Logo` --conceptually_related_to--> `StackPress README`  [INFERRED]
  assets/images/brand/logo.png → README.md
- `Application` --inherits--> `Singleton`  [EXTRACTED]
  src/Core/Application.php → src/Core/Singleton.php
- `ThemeHelper` --inherits--> `Singleton`  [EXTRACTED]
  src/Helpers/ThemeHelper.php → src/Core/Singleton.php
- `AbstractPageMeta` --inherits--> `Singleton`  [EXTRACTED]
  src/Meta/AbstractPageMeta.php → src/Core/Singleton.php
- `MetaHelper` --inherits--> `Singleton`  [EXTRACTED]
  src/Meta/MetaHelper.php → src/Core/Singleton.php

## Import Cycles
- None detected.

## Communities (54 total, 21 thin omitted)

### Community 0 - "Singleton Base & Helpers"
Cohesion: 0.05
Nodes (15): DetailKing\Theme\Core\ServiceInterface, FieldBuilderTrait, Singleton, MediaHelper, MenuHelper, TemplateHelper, GlobalFields, SiteContentMenu (+7 more)

### Community 1 - "CPT Field Groups"
Cohesion: 0.06
Nodes (5): AbstractPostTypeMeta, PostTypeMeta_Faq, PostTypeMeta_Membership, PostTypeMeta_Service, PostTypeMeta_Testimonial

### Community 2 - "Search & Lead Capture"
Cohesion: 0.09
Nodes (5): SearchService, FormRegistry, LeadPostType, WP_Post, WP_Query

### Community 3 - "Form Submission Handling"
Cohesion: 0.21
Nodes (4): FormService, WP_Error, WP_REST_Request, WP_REST_Response

### Community 8 - "Defaults Registry & Field Builder"
Cohesion: 0.19
Nodes (11): DefaultsRegistry, defaultFor(), defaultsSlug(), field(), fieldKey(), iconItemSubFields(), imageArgs(), keyNamespace() (+3 more)

### Community 11 - "Composer Autoload Config"
Cohesion: 0.15
Nodes (12): autoload, psr-4, config, optimize-autoloader, description, license, name, DetailKing\\Theme\\ (+4 more)

### Community 16 - "Defaults Providers"
Cohesion: 0.29
Nodes (3): DefaultsProvider, GlobalDefaults, HomepageDefaults

## Knowledge Gaps
- **19 isolated node(s):** `name`, `description`, `type`, `license`, `php` (+14 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **21 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Singleton` connect `Singleton Base & Helpers` to `Search & Lead Capture`, `Form Submission Handling`, `Frontend Debloating`, `Page Field Groups`, `Homepage Section Templates`, `Meta Field Readers`, `Asset Enqueueing`, `SMTP Mail Settings`, `Security Hardening`, `Breadcrumbs & Page Banner`, `Post Type Registration`, `Media Upload Sources`, `Theme Bootstrap`?**
  _High betweenness centrality (0.316) - this node is a cross-community bridge._
- **Why does `AbstractPageMeta` connect `Page Field Groups` to `Singleton Base & Helpers`, `CPT Field Groups`?**
  _High betweenness centrality (0.194) - this node is a cross-community bridge._
- **Why does `AbstractPostTypeMeta` connect `CPT Field Groups` to `Page Field Groups`?**
  _High betweenness centrality (0.127) - this node is a cross-community bridge._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _19 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Singleton Base & Helpers` be split into smaller, more focused modules?**
  _Cohesion score 0.05017921146953405 - nodes in this community are weakly interconnected._
- **Should `CPT Field Groups` be split into smaller, more focused modules?**
  _Cohesion score 0.0625 - nodes in this community are weakly interconnected._
- **Should `Search & Lead Capture` be split into smaller, more focused modules?**
  _Cohesion score 0.09 - nodes in this community are weakly interconnected._