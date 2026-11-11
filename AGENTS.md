# Henry Porter Machinery — agent guidance

## Project and migration context

- This repository is the new Laravel version of the client’s existing **Joomla** website, not a separate business or a new brand.
- Existing production site: https://henryportermachinery.co.uk/ (the www hostname is also used by existing links).
- Existing About page: https://www.henryportermachinery.co.uk/about.html.
- Local project: `G:\work\hpm`. Local website: http://localhost:8000/.
- The homepage and About page have been designed and implemented. Treat the homepage as the visual reference for subsequent pages.
- The full Joomla content/database migration and production cutover have **not** been completed. Current public pages use static PHP content arrays. Do not describe this as a completed migration.

## Stack and structure

- Laravel 13, PHP 8.3+, Blade, Livewire 4, Flux 2, and Fortify. Preserve the existing authentication, settings, and dashboard flows.
- Tailwind CSS 4 with Vite Plus; use the existing `pnpm-lock.yaml` and pnpm commands.
- Public layout: `resources/views/components/layouts/site.blade.php`.
- Reusable public components: `resources/views/components/site/`.
- Page templates: `resources/views/home.blade.php` and `resources/views/about.blade.php`.
- Static page content: `resources/data/home.php` and `resources/data/about.php`, passed to views through `routes/web.php`.
- Current routes: `/` named `home`, `/about` named `about`, and a permanent `/about.html` redirect to `/about`.
- Original homepage wireframe: `storage/app/public/wireframes/home.html`. Preserve this reference file.

## Design and Blade conventions

- Continue the established warm off-white, white, charcoal, and red visual style. Main brand red is `#c8102e`; darker and lighter variants are `#a00d24` and `#e63946`.
- Use Sora for headings/display text and Inter for body text, rounded cards, pill buttons, generous spacing, and restrained gradients.
- Prefer standard Tailwind utilities for ordinary layout, spacing, typography, responsive behavior, and states.
- For designs that would require arbitrary-value or complicated Tailwind classes, use a descriptive CSS class in a public stylesheet instead. Do not accumulate inline styles or arbitrary-value utilities.
- Reuse the site layout, container, heading, button, icon, card, and other existing components. Use props, slots, and merged attributes for reusable variations.
- Public UI icons are Material Symbols Rounded inline SVG components (`<x-icon.name />`, `<x-icon :name="..." />`) generated with `php artisan icon:add`; see `docs/icons.md`. Do not use emoji as UI icons.
- Keep repeated page content in structured arrays. Follow the existing static-content pattern until a task actually requires database-backed content.
- Public layout metadata supports `title` and `description` props; provide appropriate metadata for new pages.
- Keep public-page styling scoped appropriately and preserve the starter kit’s authentication/dashboard appearance.

## Asset workflow — explicit user preference

- **Plain CSS and browser-ready JavaScript belong in `public`, so the user can edit them and refresh without rebuilding.**
- Shared custom styles: `public/css/site.css`.
- About-specific custom styles: `public/css/about.css`, loaded only for the About route.
- Shared browser interactions: `public/js/site.js`. It is loaded directly with `defer` and handles mobile navigation, product filtering, reveal effects, and counters.
- The public layout uses file modification times as cache versions for these directly served assets. Retain that behavior for new public assets.
- Tailwind build input: `resources/css/app.css`. Build-time theme directives/tokens: `resources/css/site-theme.css`.
- Keep Tailwind directives and other code requiring compilation in `resources`; do not put unprocessed Tailwind directives into browser-served CSS.
- Rebuild when adding/changing Blade utility classes or Tailwind theme configuration. Editing plain public CSS/JS should not require a build.
- Do not hand-edit `public/build`; it contains generated output. Keep existing build entry points needed by the starter kit and passkeys.
- The original Henry and James photograph is stored locally at `public/images/about/henry-and-james.jpeg`. Several cards support an optional `imageUrl`; reuse that interface when replacing placeholders.

## Navigation and interaction behavior

- There is intentionally **no Home item in either header menu**. The clickable logo links to the homepage.
- The full header navigation is visible from **1024px** upward. Between 1024px and 1279px it uses tighter spacing; smaller widths use the mobile menu.
- Keep the header’s responsive classes and the `1024px` media query in `public/js/site.js` synchronized.
- Mobile navigation must maintain `aria-expanded`, close on Escape, restore focus to the toggle on Escape, and close when switching to desktop navigation.
- Shared navigation must work across pages. Links to homepage sections need the homepage URL plus fragment when the visitor is on another page. About links must use the named About route.
- Homepage category tiles filter the featured machinery; product filters include an empty-results state. FAQs use native `details`/`summary` behavior.
- Respect `prefers-reduced-motion`. Essential text, history, cards, and final statistics must remain readable without JavaScript.
- Newsletter signup is intentionally disabled with an unavailable message until a real backend is implemented. Do not simulate successful submissions.
- Unavailable social/policy/destination links remain non-interactive; avoid dead `href="#"` links.

## Catalogue importer

- Catalogue tables/models and the `catalogue:import-joomla` Artisan command are implemented. See `docs/catalogue-import.md` before changing the import.
- The source backup is `G:\work\hpm-joomla`, containing `db/db.sql` and `files/`. Keep this source unchanged.
- Default execution is a read-only dry run; `--apply` writes to the configured Laravel database and public storage. Confirm the database target before applying on another environment.
- `Product::catalogue()` returns published machinery only. Templates and the Parts content record are intentionally preserved but hidden.
- The audited snapshot has 60 preserved records (50 published machinery), 9 brands, 15 equipment categories, 320 registered media plus 6 description images, 215 valid gallery links, 32 videos, and 14 testimonial associations.
- Eight source gallery links point to product ID 0; they are excluded and audited while their images are retained. The invalid video custom field with ID 2 is also retained in the audit rather than rendered.
- Private reports and original catalogue snapshots live under `storage/app/private/catalogue-imports/`. Do not commit or expose them publicly.
- Legacy URL mappings remain pending. Public catalogue pages, general Joomla content migration, and production cutover are still separate work. Catalogue admin management is implemented; see `docs/catalogue-admin.md`.

## Joomla migration and content fidelity

- Review the relevant existing Joomla page before implementing its Laravel replacement. Retain useful content and business information while redesigning it to match the new site.
- Inventory actual legacy page URLs and map them to their Laravel replacements. Add explicit permanent redirects for changed URLs; retain the existing `/about.html` redirect.
- Preserve relevant titles, descriptions, slugs, internal links, image alt text, and downloadable/media assets when migrating content. Check links from both www and non-www legacy references when planning deployment.
- Inspect any supplied Joomla export, database, or extension data before designing importers. Do not assume an extension schema, invent an export, or claim records have been migrated without verifying them.
- For future import work, preserve relationships and source identifiers and make imports repeatable without duplicating records. Validate record counts and representative imported content.
- Use verified business facts. The existing About page records establishment in **2006**, the Pöttinger franchise in **2013**, James joining in **September 2018**, and the rebrand in **July 2019**.
- The homepage inherited sample products, testimonials, dates, and numerical claims from the supplied wireframe. Those are not all verified production content. In particular, do not confuse the wireframe’s “40+ years” messaging with the business’s 2006 establishment date. Resolve such claims against client-approved content before launch.
- Keep the existing Joomla site as the migration reference; do not change the live site, DNS, or hosting as an incidental part of local page work.

## Validation and working practices

- Inspect relevant files and existing changes first; preserve unrelated user work.
- Run `pnpm run build` when compiled assets are affected. `pnpm run dev` starts the frontend development server.
- Use `php artisan test --compact --filter=ExampleTest` for the existing homepage response tests; run other relevant Laravel tests for behavior changes.
- Use targeted PHP formatting, for example `php vendor/bin/pint <changed-php-files> --test`; use `node --check public/js/site.js` for plain JavaScript syntax and `git diff --check` for whitespace.
- For frontend work, verify desktop, tablet, and phone layouts and inspect screenshots. For navigation, check widths on both sides of 1024px, keyboard behavior, active links, and overlap.
- Check relevant redirects, contact links, reduced motion, no-JavaScript content, and browser exceptions. Smoke-check the homepage and authentication screens when shared code changes.
- Local exploratory browser scripts/screenshots currently live under ignored `storage/app/private/`; they are QA artifacts, not a committed test framework. Do not depend on their presence in a fresh checkout.
- Previously, sandboxed command/browser launchers reported an internal corrupted `D:` volume before reaching the project. The repository itself is on `G:`. This is an environment-specific tool issue, not a reason to change project paths; use an available permitted tool path and report any remaining verification limitation accurately.

## Catalogue admin and media conventions

- Application records use UUIDv7; keep Joomla source IDs separately. Infrastructure/session/cache/queue keys retain their framework formats.
- `/admin` uses the existing Livewire/Flux layout and requires a verified user with `role = admin`; public registration always defaults to `user`. Provision admins with `php artisan catalogue:admin`, never hardcoded credentials.
- Store entity media directly under `products/YYYY/MM-DD/{uuid}/`, `categories/YYYY/MM-DD/{uuid}/`, or `brands/YYYY/MM-DD/{uuid}/` on the public disk. The persisted folder date follows record creation and stays stable for later uploads. Do not add a `catalogue/` parent.
- Brands have separate logo and card-photo relationships; categories have an image. Never delete shared files when detaching an image.
- Admin saves mark imported aggregates as edited. Reimports skip edited/deleted aggregates unless explicitly passed `--overwrite-edited`. Preserve dry-run immutability and media rollback.
- Admin custom CSS is directly served from `public/css/admin.css`. Keep existing auth styling and public static homepage/About flows intact.

## Public catalogue and redirects

- The homepage catalogue sections are database-backed through `PublicCatalogue`; `resources/data/home.php` contains static editorial content only. Brand/category homepage visibility/order are admin controlled.
- Public canonical pages live under `/machinery`, `/categories`, `/brands`, `/news`, plus `/parts`, `/contact`, `/cookie-policy`, `/sitemap` and `/sitemap.xml`. Read `docs/public-catalogue.md` before changing URL behavior or deployment.
- Legacy redirects run globally before route matching, including old paths with no current Laravel route. Preserve explicit imported menu destinations, verified status, query context, old slug aliases, and privacy of unpublished records. Unknown URLs keep a real 404.
- Enquiries are private UUIDv7 records with an admin inbox. Notifications remain disabled until SMTP and a supervised worker are configured; do not claim email delivery based on successful database storage.
- Additional page/news assets are manifest-driven; preserve the source backup. Plain public CSS/JS remain directly editable in `public/css/catalogue.css` and `public/js/catalogue.js`.
