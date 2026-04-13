# Project context (master)

## Source of truth

Docs are the source of truth.  
All development must follow docs.  
If conflict exists:

- docs must be updated **or**
- task must be clarified.

---

## Project goal

Reusable Laravel 10 **small-business site template** with a **minimal CMS**: content and lists editable in admin without changing Blade for every client. Favor **working, stable installs** over showcase complexity.

---

## Core principles

- Simplicity > complexity  
- Performance > visual polish  
- Stability > clever patterns  
- Reusable per client; **no one-off hacks** in core paths  
- **No overengineering** (no extra layers unless a task explicitly requires them)

---

## Locked stack (verified)

**PHP / Composer (`require`):**

- PHP `^8.1`
- `laravel/framework` `^10.10`
- `guzzlehttp/guzzle` `^7.2`
- `intervention/image` `3.0`
- `laravel/sanctum` `^3.3`
- `laravel/tinker` `^2.8`

**Composer dev (tooling only):** `fakerphp/faker`, `laravel/pint`, `laravel/sail`, `mockery/mockery`, `nunomaduro/collision`, `phpunit/phpunit`, `spatie/laravel-ignition`.

**Node / `package.json` (`devDependencies`):**

- `vite` `^5.0.0`
- `laravel-vite-plugin` `^1.0.0`
- `tailwindcss` `^3.4.17`
- `postcss` `^8.5.8`
- `autoprefixer` `^10.4.27`
- `axios` `^1.6.4`

**Frontend delivery:** Vite + Tailwind; **Blade** for UI. **Minimal JS** (inline where needed). **No jQuery** in repo dependencies.

---

## Forbidden technologies (unless a task explicitly approves a change)

- React, Vue, SPA frameworks  
- New Composer/NPM packages **without explicit approval**  
- Heavy GDPR/analytics frameworks, enterprise service layers **by default**

---

## Fixed architecture

- **Public:** `PageController`-driven pages; Blade + `layouts.app`; shared header/footer/components.  
- **Admin:** `prefix /admin`, session auth; Blade + `layouts.admin`.  
- **Data:** Eloquent models + migrations; **no** parallel “shadow” config outside DB/settings for CMS content.  
- **Public contact:** `POST /contact` → validation → **persist `Inquiries`** → optional sync mail.  
- **Gallery images:** processed to WebP under `storage/app/public/gallery/`; public URLs via `storage` symlink + `asset('storage/…')`.

---

## Core entities

| Entity | Role |
|--------|------|
| **PageSection** | Per-page CMS blocks (`page` + `section`); edited in admin Sections. |
| **SiteSetting** | Key/value global settings; keys defined in `SiteSetting::KEYS`. |
| **Service** | Services list; active flag, sort order; public Services + home preview. |
| **GalleryItem** | Image (file) or video (URL); active, sort order; public Gallery + home preview. |
| **Inquiry** | Contact form submission record; statuses `received` / `notified` / `mail_failed`; admin list/show/resend. |

---

## Helpers (`app/helpers.php`)

- `setting($key, $default = null)` — request-scoped cache of `site_settings`.  
- `page_section($page, $section)` — cached `PageSection` lookup.  
- `section_url($url)` — safe href for CMS button URLs (route name, legacy path, or external).

---

## Project priorities

1. Correct data flow and **no silent data loss** (e.g. inquiries persisted before mail).  
2. **Operational deployability** (env, `storage:link`, mail, throttling).  
3. **Admin clarity** (obvious modules, minimal surprise).  
4. SEO basics (meta, sitemap, robots) without third-party SEO suites.

---

## Change rules

- Scope changes need **explicit** task update.  
- Do not refactor unrelated code.  
- Do not rename public routes or break admin URLs without a migration note in docs + README.  
- After behavior change: update **this doc** or the relevant numbered doc (`03`–`05`).

---

## Definition of done

- Feature works end-to-end in the target environment.  
- No unrelated files changed.  
- **Manual test** performed (happy path + one failure path if applicable).  
- **Commit boundary** is clear (one logical change or explicitly stacked commits with message discipline).

## ARCHITECTURE LOCK

The following are FIXED:

- Entities:
  PageSection
  SiteSetting
  Service
  GalleryItem
  Inquiry

- Core flow:
  Controller → Request → Model → Blade

Changes NOT allowed without explicit approval:
- new core models
- changing relationships
- altering data flow

## PAYMENT SYSTEM

Payment logic is defined in:
→ docs/07_PAYMENT_RULES.md

This document MUST be followed for all payment-related implementation.
