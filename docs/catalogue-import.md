# Importing the Joomla catalogue

The importer reads a CloudAccess backup containing `db/db.sql` and `files/`.
It parses allowlisted table data and never executes Joomla SQL or PHP. Do not
restore the legacy dump into the Laravel database.

## Commands

```powershell
php artisan migrate
php artisan catalogue:import-joomla "G:\work\hpm-joomla"
php artisan catalogue:import-joomla "G:\work\hpm-joomla" --apply
```

The default command only validates and reports. It makes no database, media, or
report writes. Add `--json` to print the complete report to standard output.
`--apply` imports into the configured Laravel database, copies media to the
`public` storage disk, and writes a private audit and report beneath
`storage/app/private/catalogue-imports/<run-id>/`.

Check the configured database before applying on a different machine. The command
uses the current Laravel database; it does not choose or create a database.
Keep source backups and private audit files outside Git and publicly served folders.

## Catalogue model

- `Product::catalogue()` selects only published machinery. Do not use unfiltered
  `Product::all()` for a public catalogue: preserved templates and content records
  intentionally exist in the same table.
- Brands and equipment categories are separate; new/used is a product condition.
- Products have multiple categories and ordered gallery media. `coverImage()`
  returns the first image by source order and gallery-link ID.
- `MediaAsset::url()` resolves the copied asset using Laravel's public storage.
  Imported description images on local storage use site-relative `/storage/...`
  URLs so moving the database to production does not retain localhost links.
- Videos contain YouTube IDs, not legacy iframe HTML. Testimonials and product
  descriptions are sanitised HTML, with specification tables retained.
- There are no prices, stock tracking, checkout, or orders. Source zero stock
  never hides a machine. Publication status does not assert current availability.
- Existing homepage/About views remain static; public catalogue pages remain pending. Admin editing is implemented separately; see `catalogue-admin.md`.

## Expected snapshot and review items

For the supplied snapshot, the expected output is:

| Item | Count |
| --- | ---: |
| Preserved products | 60 |
| Published machinery | 50 |
| Draft machinery | 1 |
| Hidden templates | 8 |
| Hidden Parts content record | 1 |
| Brands | 9 |
| Equipment categories | 15 |
| Registered media | 320 |
| Additional description images | 6 |
| Valid product gallery associations | 215 |
| Videos | 32 |
| Testimonial associations | 14 |

The original 223 product-media links include eight links to nonexistent product
ID 0 (source link IDs 286–293). These associations are excluded and recorded in
`unresolved_references`; their images remain in the media library and their
original rows remain in the audit. Other broken references fail validation.

The invalid video in source custom-field ID 2 has `src="#"`; it is excluded
from videos and preserved in the audit. VirtueMart's custom-field definitions,
not its instance-level `published=0` values, determine the migrated content.

Template IDs 7–12, 52, and 56 and Parts record 60 remain hidden. Product 88 remains
a draft. Sherpa Diesel records 78 and 80 remain separate with review flags. Many
real products have `template-*` source aliases: those aliases do not make them
templates. Source IDs, publication flags, aliases and SEO fields are retained.

All nine brands and 15 equipment categories are defined in `JoomlaCatalogue`.
Explicit overrides fill gaps where the old catalogue only assigned a brand or
used/new grouping. Unknown categories, conflicting brands, or unmapped machinery
fail validation rather than receiving guessed assignments.

## Reimports and failure recovery

Imports upsert stable legacy/source identifiers. Source-owned fields and imported
associations are refreshed. Manually created unrelated records, files, and manual
category associations are retained. A source product missing from a later backup
is not automatically deleted. Admin-edited or deleted aggregates are skipped by default, including their children and referenced media. Pass `--overwrite-edited` to deliberately restore source-owned fields. See `catalogue-admin.md` for UUIDv7 and dated storage conventions.

Media is path-validated, MIME-checked, checksummed and staged before database
writes. All database changes run in one transaction. Public files are published
with a recovery ledger; a failure rolls back the transaction, removes newly copied
files and restores overwritten files. Failure reports and previous-file backups
remain private. Resolve any reported `media_recovery_error` before retrying.

Legacy URL mappings are an inventory, not active redirects. Explicit public menu
routes take precedence; inferred aliases are labelled as such. All mappings stay
`pending` until their destination pages are built and verified. No live Joomla,
DNS or hosting configuration is modified.

## Verification

```powershell
php artisan test --compact --filter=JoomlaCatalogueImportTest
php vendor/bin/phpstan analyse --no-progress
```

The portable tests use a synthetic SQL backup, an isolated database, and fake
public storage. They cover escaped SQL strings, dangerous HTML, UTF-8, zero dates,
media paths, orphan links, idempotence, manual records, and injected copy failures.
Compare source/import counts and report checksums on a new full snapshot; inspect
specification tables, the used-machine gallery, and the two Sherpa records before
building public catalogue screens.
