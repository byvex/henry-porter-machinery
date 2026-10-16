# Public catalogue and legacy traffic

Public pages use the existing site layout, Blade GET forms and database catalogue models. Custom browser-ready styling/behavior lives in `public/css/catalogue.css` and `public/js/catalogue.js`; the shared homepage scripts remain in `public/js/site.js`. Plain CSS/JS edits need no rebuild. Blade utility-class changes require `pnpm run build`.

## Homepage and enquiries

Brand/category admin forms have independent homepage visibility and ordering controls. The initial selection is seven established brands and six equipment categories. Reimports initialise these only on first creation and preserve later choices. The home page shows six featured published machines (latest published machines if none are featured), three latest used machines, real source testimonials, and the migrated news archive. No sample product or customer records are used.

Public product, category and brand URLs use readable slugs. Saving an admin slug change creates a permanent old-slug mapping; conflicting aliases are reserved. Templates, content records and drafts remain private. Empty active categories are legitimate public pages.

Enquiries are saved privately in the database with UUIDv7, product snapshots and idempotent submission tokens. The admin inbox is `/admin/enquiries`; only verified administrators can access it. Email is optional and disabled by default. Submissions work without JavaScript and without SMTP. Rate limits and a honeypot protect the form. No attachments or automatic customer emails are sent.

To enable business notification emails after testing the server mail configuration, set `ENQUIRY_NOTIFICATIONS_ENABLED=true` and `ENQUIRY_RECIPIENT=info@henryportermachinery.co.uk`, configure a delivery mailer, refresh config cache and run a supervised queue worker (`php artisan queue:work --tries=3`). Notification failures leave the enquiry safely stored; delivery status is visible in the inbox.

## Legacy URL coverage

`resources/data/legacy-sitemap.json` is the versioned 222-path inventory from the old sitemap. Imported menu destinations take precedence over inference. Alternate product/category/brand paths, news, article aliases, Parts, Contact and Joomla query forms map to canonical internal destinations. Unavailable known products lead to related listings; unknown paths remain useful 404 pages.

Run `php artisan catalogue:verify-legacy-urls` for a destination dry run. Add `--apply` to save/activate valid mappings, check actual source responses and write `storage/app/private/legacy-urls/report.json`. The report includes source URL, final target and HTTP status. The current snapshot has 431 mappings plus existing canonical sitemap paths. Reimports retain verification when target identity/filter context is unchanged. Rerun verification after changes to page availability.

## Additional legacy page assets

Four news articles, Parts content and the information policy are reviewed structured content in `resources/data/legacy-pages.json`. Original publication dates are retained. The old analytics statements were updated to match this implementation, which has no analytics tracker and loads YouTube only on request. General page/news editing remains outside the admin scope for now.

`resources/data/legacy-page-assets.json` lists the nine required source assets, target paths and SHA-256 checksums. These include selected `files/media/k2/items/cache`, `files/media/widgetkit` and Parts images. Upload only these additional paths alongside the earlier catalogue-image package. Preserve case and filenames. Keep the original SQL/backup outside the public web directory.

Validate with `php artisan site:copy-legacy-assets /private/backups/hpm-joomla`. Add `--apply` to stage and copy files to the public disk with checksum verification and rollback. The source is never modified and Joomla PHP is never executed. The manufacturer-hosted Parts brochure remains an external verified link.

## Deployment and cutover

Keep existing Laravel data, APP_KEY and media. This change uses additive migrations; never run `migrate:fresh` on the server. Set APP_URL to the final canonical HTTPS origin. Serve only Laravel's `public` directory. Point both www/non-www hosts to this application; configure the web server to route legacy paths, including `.html` and `/index.php?...`, through Laravel. The supplied Apache rewrite file exempts legacy filtering/catalogue paths from premature trailing-slash redirects. For Nginx, use `try_files $uri $uri/ /index.php?$query_string` and avoid slash-normalising those old paths first. Configure trusted proxies for accurate scheme/client IP when behind a reverse proxy. Host/HTTPS redirects should be coordinated with the application redirect inventory so old URLs do not accumulate avoidable chains. Do not install Joomla in the Laravel public directory.

After deploying code/dependencies and production assets:

```sh
php artisan migrate --force
php artisan storage:link
php artisan catalogue:import-joomla /private/backups/hpm-joomla
php artisan catalogue:import-joomla /private/backups/hpm-joomla --apply
php artisan site:copy-legacy-assets /private/backups/hpm-joomla --apply
php artisan catalogue:verify-legacy-urls --apply
php artisan config:cache
php artisan view:cache
```

If the catalogue is already imported, rerunning the importer is optional; admin-edited aggregates are protected by default. Never add `--overwrite-edited` as a routine deployment step. Provision an administrator with `php artisan catalogue:admin` if needed.

Before Joomla retirement, check the report on production, real enquiry delivery, canonical host, storage permissions, source asset checksums, sitemap XML and representative old URLs. Keep a complete recoverable backup. DNS/hosting changes and Joomla removal are not performed by this implementation.
